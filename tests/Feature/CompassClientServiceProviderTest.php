<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient\Tests\Feature;

use Carbon\CarbonImmutable;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Events\MaintenanceModeDisabled;
use Illuminate\Foundation\Events\MaintenanceModeEnabled;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Routing\Route;
use Illuminate\Routing\Router;
use Laravel\Nightwatch\Http\Middleware\Sample;
use Maarheeze\CompassClient\CompassApiUrl;
use Maarheeze\CompassClient\Http\RouteName;
use Maarheeze\CompassClient\SchedulerLastRun;
use Maarheeze\CompassClient\Tests\TestCase;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;

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

        $response = $this->withToken($key)->postJson(route(RouteName::STATUS));

        $response->assertOk();
    }

    public function testBootLeavesTheStatusOutOfNightwatch(): void
    {
        $router = $this->app->make(Router::class);

        $route = $router->getRoutes()->getByName(RouteName::STATUS->value);

        $this->assertInstanceOf(Route::class, $route);
        $this->assertContains(Sample::never(), $route->middleware());
    }

    public function testBootRequestsAnInspectionWhenMaintenanceModeIsEnabled(): void
    {
        $http = $this->app->make(HttpFactory::class);
        $http->fake();
        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));
        $compassApiUrl->remember(fake()->url(), $validUntil);
        $this->setConfiguration('compass-client.key', fake()->sha1());

        $this->app->make(Dispatcher::class)->dispatch(new MaintenanceModeEnabled());

        $http->assertSentCount(1);
    }

    public function testBootRequestsAnInspectionWhenMaintenanceModeIsDisabled(): void
    {
        $http = $this->app->make(HttpFactory::class);
        $http->fake();
        $compassApiUrl = $this->app->make(CompassApiUrl::class);
        $validUntil = CarbonImmutable::instance(fake()->dateTimeBetween('+1 hour', '+1 day'));
        $compassApiUrl->remember(fake()->url(), $validUntil);
        $this->setConfiguration('compass-client.key', fake()->sha1());

        $this->app->make(Dispatcher::class)->dispatch(new MaintenanceModeDisabled());

        $http->assertSentCount(1);
    }

    public function testBootRecordsASchedulerRunWhenTheScheduleRunStarts(): void
    {
        $this->app->make(Dispatcher::class)->dispatch(
            new CommandStarting('schedule:run', new ArrayInput([]), new NullOutput()),
        );

        $schedulerLastRun = $this->app->make(SchedulerLastRun::class);
        $this->assertNotNull($schedulerLastRun->lastRanAt());
    }
}
