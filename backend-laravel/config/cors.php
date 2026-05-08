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

    'paths' => ['api/*', 'sanctum/csrf-cookie'],

    'allowed_methods' => ['*'], // Cho phép tất cả các method GET, POST, DELETE...

    'allowed_origins' => ['*'], // QUAN TRỌNG NHẤT: Cho phép tất cả các domain gọi vào

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'], // Cho phép tất cả các loại headers

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
