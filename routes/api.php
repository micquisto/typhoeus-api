<?php

/**
 * Typhoeus API api routes
 */
use Typhoeus\Api\Controllers\ApiProductsController;
use Typhoeus\Api\Controllers\Typhoeus\ApiOrdersController;
use Typhoeus\Api\Controllers\Typhoeus\ApiAdminController;

Route::middleware('api-middleware')->group(function () {
    Route::middleware('api-product-middleware')->group(function () {
        Route::post('/ps-api/products', [ApiProductsController::class, 'getProducts']);
    });
    Route::post('/ps-api/orders', [ApiOrdersController::class, 'process']);
});

Route::post('/ps-api/admin', [ApiAdminController::class, 'process']);

