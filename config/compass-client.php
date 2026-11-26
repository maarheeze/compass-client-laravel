<?php

declare(strict_types=1);

use Webmozart\Assert\Assert;

return [

    /*
    |--------------------------------------------------------------------------
    | Path
    |--------------------------------------------------------------------------
    |
    | The URI Compass reads the status of this site from.
    |
    */

    'path' => env('COMPASS_CLIENT_PATH', '/_compass'),

    /*
    |--------------------------------------------------------------------------
    | Nightwatch ignore
    |--------------------------------------------------------------------------
    |
    | Whether the requests from Compass are left out of Laravel Nightwatch.
    | It only takes effect when Nightwatch is installed.
    |
    */

    'nightwatch_ignore' => env('COMPASS_CLIENT_NIGHTWATCH_IGNORE', true),

    /*
    |--------------------------------------------------------------------------
    | Key
    |--------------------------------------------------------------------------
    |
    | The key Compass sends as a bearer token. Compass generates it for this
    | site and shows it on the site's page. Without a key every request is
    | refused.
    |
    */

    'key' => env('COMPASS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Compass API timeout
    |--------------------------------------------------------------------------
    |
    | The number of seconds the site waits for Compass when it asks Compass
    | to inspect it again, on `php artisan down` and `up`. Those commands
    | wait at most this long.
    |
    */

    'compass_api_timeout_seconds' => (int) Assert::integerish(env('COMPASS_CLIENT_COMPASS_API_TIMEOUT_SECONDS', 10)),

    /*
    |--------------------------------------------------------------------------
    | Uptime checks
    |--------------------------------------------------------------------------
    |
    | What Compass checks for uptime: paths, and the names of routes that are
    | reported as their paths. Each is checked every 15 minutes, or at the
    | number of minutes given with it: '/' => 30.
    |
    */

    'uptime_checks' => [
        'paths' => [],
        'routes' => [],
    ],

];
