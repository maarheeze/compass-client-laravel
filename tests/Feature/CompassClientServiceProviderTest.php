<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature;

use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Laravel\Nightwatch\Http\Middleware\Sample;
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

    public function testBootLeavesTheStatusOutOfNightwatch(): void
    {
        $router = $this->app->make(Router::class);

        $route = $router->getRoutes()->getByName(RouteName::STATUS->value);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertContains(Sample::never(), $route->middleware());
    }
}
