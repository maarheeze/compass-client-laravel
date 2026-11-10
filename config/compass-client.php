<?php

declare(strict_types=1);

use Webmozart\Assert\Assert;

$endpoints = env('COMPASS_CLIENT_ENDPOINTS', '/');
Assert::string($endpoints);

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
    | Endpoints
    |--------------------------------------------------------------------------
    |
    | The paths of the endpoints Compass checks for uptime. In the environment
    | variable they are separated by a comma.
    |
    */

    'endpoints' => array_map(trim(...), explode(',', $endpoints)),

];
