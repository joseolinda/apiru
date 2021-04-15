<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel CORS Options
    |--------------------------------------------------------------------------
    |
    | The allowed_methods and allowed_headers options are case-insensitive.
    |
    | You don't need to provide both allowed_origins and allowed_origins_patterns.
    | If one of the strings passed matches, it is considered a valid origin.
    |
    | If array('*') is provided to allowed_methods, allowed_origins or allowed_headers
    | all methods / origins / headers are allowed.
    |
    */

    /*
     * You can enable CORS for 1 or multiple paths.
     * Example: ['api/*']
     */
    'paths' => ['api/*'],

    /*
    * Matches the request method. `[*]` allows all methods.
    */
    'allowed_methods' => ['GET, POST, PUT, DELETE, OPTIONS'],

    /*
     * Matches the request origin. `[*]` allows all origins. Wildcards can be u$
     */
    'allowed_origins' => ['http://localhost:3000'],

    /*
     * Patterns that can be used with `preg_match` to match the origin.
     */
    'allowed_origins_patterns' => ['Content-Type',
        'Origin','Authorization'],

    /*
     * Sets the Access-Control-Allow-Headers response header. `[*]` allows all $
     */
    'allowed_headers' => ['*'],

    /*
     * Sets the Access-Control-Expose-Headers response header with these header$
     */
    'exposed_headers' => ['Authorization'],

    /*
     * Sets the Access-Control-Max-Age response header when > 0.
     */
    'max_age' => 3600,

    /*
     * Sets the Access-Control-Allow-Credentials header.
     */
    'supports_credentials' => false,

];
