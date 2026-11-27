# maarheeze/compass-client-laravel

Exposes the status of a Laravel site to [Compass](https://github.com/maarheeze/compass). It registers a single route that Compass reads, guarded by a bearer token and reachable during maintenance mode.

## Installation

```bash
composer require maarheeze/compass-client-laravel
```

The package auto-registers via Laravel's package discovery.

## Configuration

Set the key Compass sends as a bearer token. Compass generates it for this site and shows it on the site's page:

```dotenv
COMPASS_CLIENT_KEY=your-key
```

If you want to override the path the status is served at, which defaults to `/_compass`:

```dotenv
COMPASS_CLIENT_PATH=/_compass
```

When [Laravel Nightwatch](https://nightwatch.laravel.com) is installed, the requests from Compass are left out of it. To record them anyway:

```dotenv
COMPASS_CLIENT_NIGHTWATCH_IGNORE=false
```

### Publishing the config

To customize settings beyond the environment variables, publish the config file to `config/compass-client.php`:

```bash
php artisan vendor:publish --tag=compass-client-config
```

### Uptime checks

The paths and the names of the routes Compass checks for uptime are set in the published config. Each is checked every 15 minutes, or at the number of minutes given with it. A route is reported as its path, so a route with required parameters cannot be used. By default nothing is checked. For example:

```php
'uptime_checks' => [
    'paths' => [
        '/',
        '/contact' => 60,
    ],
    'routes' => [
        'home' => 5,
        RouteName::CHECKOUT,
    ],
],
```

### Scheduler and queue

The status reports the tasks in the site's schedule, when the scheduler last ran and the driver of the default queue connection, so Compass can tell whether the scheduler and a queue worker are needed and running. The scheduler's last run is recorded each time `schedule:run` starts, in the default cache store, so the scheduler and the web server need to share that store, as a database or Redis store does. No task is added to the site's schedule.

## License

MIT
