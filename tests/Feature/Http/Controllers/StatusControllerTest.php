<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Http\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Routing\Router;
use Illuminate\Routing\UrlGenerator;
use InvalidArgumentException;
use Maarheeze\CompassClient\Http\RouteName;
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

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

        $response->assertExactJson([
            'app_debug' => $debug,
            'app_env' => $environment,
            'app_name' => $name,
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
        ]);
    }

    public function testEndpointsCheckAPathWithoutAFrequencyEveryFifteenMinutes(): void
    {
        $key = fake()->sha1();
        $path = sprintf('/%s', fake()->slug());

        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', [$path]);

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

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

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

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

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

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

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

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

        $this->withToken($key)->getJson(route(RouteName::STATUS));
    }

    public function testStatusContainsTheValuesOfABoundStatus(): void
    {
        $key = fake()->sha1();
        $value = fake()->word();
        $this->setConfiguration('compass-client.key', $key);
        $this->app->bind(Status::class, static function (Application $app) use ($value): Status {
            return new class ($app->make(Repository::class), $app->make(UrlGenerator::class), $value) extends Status {
                public function __construct(
                    Repository $config,
                    UrlGenerator $urlGenerator,
                    private readonly string $value,
                ) {
                    parent::__construct($config, $urlGenerator);
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

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

        $response->assertJsonPath('custom', $value);
        $response->assertJsonStructure([
            'app_debug',
            'app_env',
            'app_name',
            'endpoints',
        ]);
    }
}
