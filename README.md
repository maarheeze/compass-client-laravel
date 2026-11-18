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

The paths and the names of the routes Compass checks for uptime are set in the published config. Each is checked every 15 minutes, or at the number of minutes given with it. A route is reported as its path, so a route with required parameters cannot be used. By default only `/` is checked:

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

## License

MIT
