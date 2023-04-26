<?php

/**
 * Custom middleware configuration - load your middleware here together with alias or group
 */

return [
    'route' => [
         'api-middleware' => \Typhoeus\Api\Middleware\ApiMiddleware::class,
    ],
    'groups' => [
        // 'api' => \Typhoeus\Api\Middleware\ApiMiddlewareGroup::class,
    ]
];
