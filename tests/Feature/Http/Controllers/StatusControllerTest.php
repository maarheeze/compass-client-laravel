<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Http\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\UrlGenerator;
use Illuminate\Routing\Router;
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
        $endpoints = [
            fake()->slug(),
            fake()->slug(),
        ];

        $this->setConfiguration('app.debug', $debug);
        $this->setConfiguration('app.env', $environment);
        $this->setConfiguration('app.name', $name);
        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.uptime_checks.paths', $endpoints);

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

        $response->assertExactJson([
            'app_debug' => $debug,
            'app_env' => $environment,
            'app_name' => $name,
            'endpoints' => $endpoints,
        ]);
    }

    public function testEndpointsContainThePathsOfTheUptimeCheckRoutes(): void
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

        $response->assertJsonPath('endpoints', [$path]);
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
