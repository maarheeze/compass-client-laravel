<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use Carbon\FactoryImmutable;
use Illuminate\Config\Repository;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Events\MaintenanceModeDisabled;
use Illuminate\Foundation\Events\MaintenanceModeEnabled;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Laravel\Nightwatch\Http\Middleware\Sample;
use Maarheeze\CompassClient\Http\Controllers\StatusController;
use Maarheeze\CompassClient\Http\Middleware\AuthenticateCompass;
use Maarheeze\CompassClient\Http\RouteName;
use Maarheeze\CompassClient\Listeners\RecordSchedulerRun;
use Maarheeze\CompassClient\Listeners\RequestInspection;
use Psr\Clock\ClockInterface;

use function class_exists;
use function config_path;
use function sprintf;

class CompassClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/compass-client.php', 'compass-client');

        $this->app->bindIf(ClockInterface::class, FactoryImmutable::class);
    }

    public function boot(Repository $config, Dispatcher $events, Router $router): void
    {
        $path = $config->string('compass-client.path');

        $this->listenForMaintenanceModeChanges($events);
        $this->listenForSchedulerRuns($events);
        $this->registerStatusRoute($config, $router, $path);
        $this->excludeFromMaintenanceMode($path);
        $this->publishConfig();
    }

    private function excludeFromMaintenanceMode(string $path): void
    {
        PreventRequestsDuringMaintenance::except($path);
    }

    private function listenForMaintenanceModeChanges(Dispatcher $events): void
    {
        $requestInspectionEvents = [
            MaintenanceModeDisabled::class,
            MaintenanceModeEnabled::class,
        ];
        $events->listen($requestInspectionEvents, RequestInspection::class);
    }

    private function listenForSchedulerRuns(Dispatcher $events): void
    {
        $events->listen(CommandStarting::class, RecordSchedulerRun::class);
    }

    private function publishConfig(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            sprintf('%s/../config/compass-client.php', __DIR__) => config_path('compass-client.php'),
        ], 'compass-client-config');
    }

    private function registerStatusRoute(Repository $config, Router $router, string $path): void
    {
        $route = $router->post($path, StatusController::class)->name(RouteName::STATUS);

        if ($config->boolean('compass-client.nightwatch_ignore') && class_exists(Sample::class)) {
            $route->middleware(Sample::never());
        }

        $route->middleware(AuthenticateCompass::class);
    }
}
