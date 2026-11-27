<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Http\Controllers;

use Carbon\CarbonImmutable;
use Illuminate\Config\Repository;
use Illuminate\Console\Application as ConsoleApplication;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel as ConsoleKernel;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Foundation\MaintenanceMode;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use InvalidArgumentException;
use Maarheeze\CompassClient\CompassApiUrl;
use Maarheeze\CompassClient\Http\RouteName;
use Maarheeze\CompassClient\SchedulerLastRun;
use Maarheeze\CompassClient\Status;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;
use function route;
use function sprintf;

class StatusControllerTest extends TestCase
{
    public function testStatusContainsTheApplicationAndTheEndpoints(): void
    {
        $key = fake()->sha1();
        $debug = fake()->boolean();
        $environment = fake()->word();
        $name = fake()->company();
        $paths = [
            sprintf('/%s', fake()->unique()->slug()),
            sprintf('/%s', fake()->unique()->slug()),
        ];
        $frequenciesInMinutes = [
            fake()->numberBetween(1, 60),
            fake()->numberBetween(1, 60),
        ];

        $this->setConfiguration('app.debug', $debug);
        $this->setConfiguration('app.env', $environment);
        $this->setConfiguration('app.name', $name);
        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', [
            $paths[0] => $frequenciesInMinutes[0],
            $paths[1] => $frequenciesInMinutes[1],
        ]);
        $this->setConfiguration('queue.default', 'sync');

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertExactJson([
            'app_debug' => $debug,
            'app_env' => $environment,
            'app_name' => $name,
            'maintenance_mode' => false,
            'endpoints' => [
                [
                    'path' => $paths[0],
                    'frequency_minutes' => $frequenciesInMinutes[0],
                ],
                [
                    'path' => $paths[1],
                    'frequency_minutes' => $frequenciesInMinutes[1],
                ],
            ],
            'scheduler' => [
                'last_ran_at' => null,
                'tasks' => [],
            ],
            'queue_driver' => 'sync',
        ]);
    }

    public function testSchedulerContainsTheTasksScheduledOnceArtisanStarts(): void
    {
        $key = fake()->sha1();
        $command = fake()->slug();
        $this->setConfiguration('compass-client.key', $key);
        ConsoleApplication::starting(static function (ConsoleApplication $artisan) use ($command): void {
            $artisan->getLaravel()
                ->make(Schedule::class)
                ->command($command)
                ->daily();
        });

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('scheduler.tasks', [
            [
                'command' => sprintf('php artisan %s', $command),
                'expression' => '0 0 * * *',
            ],
        ]);
    }

    public function testSchedulerNamesAClosureTaskByItsDescription(): void
    {
        $key = fake()->sha1();
        $description = fake()->sentence();
        $this->setConfiguration('compass-client.key', $key);
        $this->app
            ->make(Schedule::class)
            ->call(static function (): void {
            })
            ->description($description);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('scheduler.tasks.0.command', $description);
    }

    public function testSchedulerLastRanAtIsTheRememberedRun(): void
    {
        $key = fake()->sha1();
        $startedAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
        $this->setConfiguration('compass-client.key', $key);
        $schedulerLastRun = $this->app->make(SchedulerLastRun::class);
        $schedulerLastRun->remember($startedAt);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('scheduler.last_ran_at', $startedAt->toIso8601String());
    }

    public function testQueueDriverIsTheDriverOfTheDefaultConnection(): void
    {
        $key = fake()->sha1();
        $connection = fake()->slug();
        $driver = fake()->slug();
        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('queue.default', $connection);
        $this->app->make(Repository::class)->set(sprintf('queue.connections.%s', $connection), ['driver' => $driver]);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('queue_driver', $driver);
    }

    public function testStatusReportsMaintenanceModeWhileTheSiteIsDown(): void
    {
        $key = fake()->sha1();
        $this->setConfiguration('app.maintenance.driver', 'array');
        $this->setConfiguration('compass-client.key', $key);
        $this->app->maintenanceMode()->activate([]);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('maintenance_mode', true);
    }

    public function testStatusRemembersTheCompassApiUrl(): void
    {
        $key = fake()->sha1();
        $url = fake()->url();
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));
        $this->setConfiguration('compass-client.key', $key);

        $this->withToken($key)->postJson(route(RouteName::STATUS), [
            'compass_api_url' => $url,
            'compass_api_url_valid_until' => $validUntil->toIso8601String(),
        ]);

        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $this->assertSame($url, $compassApiUrl->url());
    }

    public function testStatusKeepsTheRememberedCompassApiUrlWhenNoneIsSent(): void
    {
        $key = fake()->sha1();
        $rememberedUrl = fake()->url();
        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));
        $compassApiUrl->remember($rememberedUrl, $validUntil);
        $this->setConfiguration('compass-client.key', $key);

        $this->withToken($key)->postJson(route(RouteName::STATUS));

        $this->assertSame($rememberedUrl, $compassApiUrl->url());
    }

    public function testStatusRefusesACompassApiUrlWithoutItsValidUntil(): void
    {
        $key = fake()->sha1();
        $this->setConfiguration('compass-client.key', $key);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS), [
            'compass_api_url' => fake()->url(),
        ]);

        $response->assertUnprocessable();
    }

    public function testEndpointsCheckAPathWithoutAFrequencyEveryFifteenMinutes(): void
    {
        $key = fake()->sha1();
        $path = sprintf('/%s', fake()->slug());

        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', [$path]);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('endpoints', [
            [
                'path' => $path,
                'frequency_minutes' => 15,
            ],
        ]);
    }

    public function testEndpointsContainThePathsOfTheUptimeCheckRoutes(): void
    {
        $key = fake()->sha1();
        $path = sprintf('/%s', fake()->slug());
        $routeName = fake()->slug();
        $frequencyMinutes = fake()->numberBetween(1, 60);

        $router = $this->app->make(Router::class);
        $router->get($path, static function (): null {
            return null;
        })->name($routeName);
        $router->getRoutes()->refreshNameLookups();

        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', []);
        $this->setConfiguration('compass-client.uptime_checks.routes', [$routeName => $frequencyMinutes]);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('endpoints', [
            [
                'path' => $path,
                'frequency_minutes' => $frequencyMinutes,
            ],
        ]);
    }

    public function testEndpointsCheckARouteWithoutAFrequencyEveryFifteenMinutes(): void
    {
        $key = fake()->sha1();
        $path = sprintf('/%s', fake()->slug());
        $routeName = fake()->slug();

        $router = $this->app->make(Router::class);
        $router->get($path, static function (): null {
            return null;
        })->name($routeName);
        $router->getRoutes()->refreshNameLookups();

        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', []);
        $this->setConfiguration('compass-client.uptime_checks.routes', [$routeName]);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('endpoints', [
            [
                'path' => $path,
                'frequency_minutes' => 15,
            ],
        ]);
    }

    public function testEndpointsContainThePathsOfTheUptimeCheckEnumRoutes(): void
    {
        $key = fake()->sha1();

        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', []);
        $this->setConfiguration('compass-client.uptime_checks.routes', [RouteName::STATUS]);

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('endpoints', [
            [
                'path' => route(RouteName::STATUS, [], false),
                'frequency_minutes' => 15,
            ],
        ]);
    }

    public function testEndpointsRefuseAFrequencyBelowOneMinute(): void
    {
        $key = fake()->sha1();

        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', [
            sprintf('/%s', fake()->slug()) => fake()->numberBetween(-60, 0),
        ]);

        $this->withoutExceptionHandling();
        $this->expectException(InvalidArgumentException::class);

        $this->withToken($key)->postJson(route(RouteName::STATUS));
    }

    public function testStatusContainsTheValuesOfABoundStatus(): void
    {
        $key = fake()->sha1();
        $value = fake()->word();
        $this->setConfiguration('compass-client.key', $key);
        $this->app->bind(Status::class, static function (Application $app) use ($value): Status {
            return new class (
                $app->make(Repository::class),
                $app->make(ConsoleKernel::class),
                $app->make(MaintenanceMode::class),
                $app->make(Schedule::class),
                $app->make(SchedulerLastRun::class),
                $app->make(UrlGenerator::class),
                $value,
            ) extends Status {
                public function __construct(
                    Repository $config,
                    ConsoleKernel $consoleKernel,
                    MaintenanceMode $maintenanceMode,
                    Schedule $schedule,
                    SchedulerLastRun $schedulerLastRun,
                    UrlGenerator $urlGenerator,
                    private readonly string $value,
                ) {
                    parent::__construct(
                        $config,
                        $consoleKernel,
                        $maintenanceMode,
                        $schedule,
                        $schedulerLastRun,
                        $urlGenerator,
                    );
                }

                /**
                 * @return array<string, mixed>
                 */
                public function toArray(): array
                {
                    return [
                        ...parent::toArray(),
                        'custom' => $this->value,
                    ];
                }
            };
        });

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertJsonPath('custom', $value);
        $response->assertJsonStructure([
            'app_debug',
            'app_env',
            'app_name',
            'maintenance_mode',
            'endpoints',
            'scheduler',
            'queue_driver',
        ]);
    }
}
