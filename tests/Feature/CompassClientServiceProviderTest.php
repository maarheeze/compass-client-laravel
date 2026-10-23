<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature;

use Maarheeze\CompassClient\Http\RouteName;
use Maarheeze\CompassClient\Tests\TestCase;

use function fake;
use function route;

class CompassClientServiceProviderTest extends TestCase
{
    public function testBootLetsTheStatusThroughDuringMaintenance(): void
    {
        $key = fake()->sha1();
        $this->setConfiguration('app.maintenance.driver', 'array');
        $this->setConfiguration('compass-client.key', $key);
        $this->app->maintenanceMode()->activate([]);

        $response = $this->withToken($key)->getJson(route(RouteName::STATUS));

        $response->assertOk();
    }
}
