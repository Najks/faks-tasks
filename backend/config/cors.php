<?php

return [
    // Origins that are allowed to access the API. Separate multiple entries with commas.
    'allowed_origins' => array_filter(array_map('trim', explode(',', env('FRONTEND_URLS', 'http://localhost:5173,http://127.0.0.1:5173')))),

    // HTTP methods that are accepted when the browser performs a CORS request.
    'allowed_methods' => ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'],

    // Headers the browser is allowed to send and receive.
    'allowed_headers' => ['Content-Type', 'Accept', 'Authorization', 'X-Requested-With'],
    'exposed_headers' => ['Link', 'X-Total-Count'],

    'supports_credentials' => true,
    'max_age' => 0,

    // CORS is typically applied only to API routes.
    'paths' => ['api/*', 'sanctum/csrf-cookie'],
];

