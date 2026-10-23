<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature\Http\Controllers;

use Maarheeze\CompassClient\Http\RouteName;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;
use function implode;
use function route;

class StatusControllerTest extends TestCase
{
    public function testStatusContainsTheApplicationAndThePages(): void
    {
        $key = fake()->sha1();
        $debug = fake()->boolean();
        $environment = fake()->word();
        $name = fake()->company();
        $pages = [fake()->slug(), fake()->slug()];
        $this->setConfiguration('app.debug', $debug);
        $this->setConfiguration('app.env', $environment);
        $this->setConfiguration('app.name', $name);
        $this->setConfiguration('compass-client.key', $key);
        $this->setConfiguration('compass-client.pages', implode(', ', $pages));

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

        $response->assertExactJson([
            'app_debug' => $debug,
            'app_env' => $environment,
            'app_name' => $name,
            'pages' => $pages,
        ]);
    }
}
