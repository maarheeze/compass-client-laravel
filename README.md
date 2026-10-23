# maarheeze/compass-client-laravel

Exposes the status of a Laravel site to [Compass](https://github.com/maarheeze/compass). It registers a single route that Compass reads, guarded by a bearer token and reachable during maintenance mode.

## Installation

```bash
composer require maarheeze/compass-client-laravel
```

The package auto-registers via Laravel's package discovery.

## Configuration

Set the key Compass sends as a bearer token. It is the same key on every site and in Compass itself:

```dotenv
COMPASS_CLIENT_KEY=your-key
```

If you want to override the path the status is served at, which defaults to `/_compass`:

```dotenv
COMPASS_CLIENT_PATH=/_compass
```

The pages Compass checks for uptime, separated by a comma, defaults to `/`:

```dotenv
COMPASS_CLIENT_PAGES=/,/contact
```

### Publishing the config

To customize settings beyond the environment variables, publish the config file to `config/compass-client.php`:

```bash
php artisan vendor:publish --tag=compass-client-config
```

## License

MIT
