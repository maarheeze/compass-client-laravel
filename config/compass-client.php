<?php

declare(strict_types=1);

use Webmozart\Assert\Assert;

$uptimeCheckPaths = env('COMPASS_CLIENT_UPTIME_CHECK_PATHS', '/');
Assert::string($uptimeCheckPaths);

$uptimeCheckRoutes = env('COMPASS_CLIENT_UPTIME_CHECK_ROUTES', '');
Assert::string($uptimeCheckRoutes);

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
    | The key Compass sends as a bearer token. It is the same key on every
    | site and in Compass itself. Without a key every request is refused.
    |
    */

    'key' => env('COMPASS_CLIENT_KEY'),

    /*
    |--------------------------------------------------------------------------
    | Uptime checks
    |--------------------------------------------------------------------------
    |
    | What Compass checks for uptime: paths, and the names of routes that are
    | reported as their paths. In the environment variables they are
    | separated by a comma.
    |
    */

    'uptime_checks' => [
        'paths' => array_values(array_filter(array_map(trim(...), explode(',', $uptimeCheckPaths)))),
        'routes' => array_values(array_filter(array_map(trim(...), explode(',', $uptimeCheckRoutes)))),
    ],

];
