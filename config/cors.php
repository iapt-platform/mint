<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Here you may configure your settings for cross-origin resource sharing
    | or "CORS". This determines what cross-origin operations may execute
    | in web browsers. You are free to adjust these settings as needed.
    |
    | To learn more: https://developer.mozilla.org/en-US/docs/Web/HTTP/CORS
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['GET','POST','PUT','DELETE','PATCH'],

    'allowed_origins' => explode(',',env('CORS_ALLOWED_ORIGINS', 'http://127.0.0.1:3000')),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['content-type','cookie','authorization','x-requested-with','accept-language'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => true,

];
