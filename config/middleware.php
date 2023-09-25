<?php

/**
 * Custom middleware configuration - load your middleware here together with alias or group
 */

return [
    'route' => [
        'api-middleware' => \Typhoeus\Api\Middleware\ApiMiddleware::class,
        'api-product-middleware' => \Typhoeus\Api\Middleware\ApiProductMiddleware::class,
    ],
    'groups' => [
        // 'api' => \Typhoeus\Api\Middleware\ApiMiddlewareGroup::class,
    ]
];
