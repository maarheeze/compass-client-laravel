<?php

declare(strict_types=1);

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
    | Uptime checks
    |--------------------------------------------------------------------------
    |
    | What Compass checks for uptime: paths, and the names of routes that are
    | reported as their paths. Each is checked every 15 minutes, or at the
    | number of minutes given with it: '/' => 30.
    |
    */

    'uptime_checks' => [
        'paths' => [
            '/',
        ],
        'routes' => [],
    ],

];
