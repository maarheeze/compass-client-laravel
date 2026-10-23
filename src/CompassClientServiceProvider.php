<?php

declare(strict_types=1);

namespace Maarheeze\CompassClient;

use Illuminate\Config\Repository;
use Illuminate\Foundation\Http\Middleware\PreventRequestsDuringMaintenance;
use Illuminate\Routing\Router;
use Illuminate\Support\ServiceProvider;
use Maarheeze\CompassClient\Http\Controllers\StatusController;
use Maarheeze\CompassClient\Http\Middleware\AuthenticateCompass;
use Maarheeze\CompassClient\Http\RouteName;

use function config_path;
use function sprintf;

class CompassClientServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/compass-client.php', 'compass-client');
    }

    public function boot(Repository $config, Router $router): void
    {
        $path = $config->string('compass-client.path');

        $router->get($path, StatusController::class)
            ->middleware(AuthenticateCompass::class)
            ->name(RouteName::STATUS);
        PreventRequestsDuringMaintenance::except($path);

        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            sprintf('%s/../config/compass-client.php', __DIR__) => config_path('compass-client.php'),
        ], 'compass-client-config');
    }
}
