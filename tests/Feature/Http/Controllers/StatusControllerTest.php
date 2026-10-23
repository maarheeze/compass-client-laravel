<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Http\Controllers;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\Application;
use Maarheeze\CompassClient\Http\RouteName;
use Maarheeze\CompassClient\Status;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;
use function implode;
use function route;

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
        $this->setConfiguration('compass-client.endpoints', implode(', ', $endpoints));

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

        $response->assertExactJson([
            'app_debug' => $debug,
            'app_env' => $environment,
            'app_name' => $name,
            'endpoints' => $endpoints,
        ]);
    }

    public function testStatusContainsTheValuesOfABoundStatus(): void
    {
        $key = fake()->sha1();
        $value = fake()->word();
        $this->setConfiguration('compass-client.key', $key);
        $this->app->bind(Status::class, static function (Application $app) use ($value): Status {
            return new class ($app->make(Repository::class), $value) extends Status {
                public function __construct(
                    Repository $config,
                    private readonly string $value,
                ) {
                    parent::__construct($config);
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
