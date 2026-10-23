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
    | The paths of the endpoints Compass checks for uptime, separated by a comma.
    |
    */

    'endpoints' => env('COMPASS_CLIENT_ENDPOINTS', '/'),

];
