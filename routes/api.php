<?php

/**
 * Typhoeus API api routes
 */
use Typhoeus\Api\Controllers\ApiProductsController;
Route::middleware('api-middleware')->group(function () {
    Route::post('/ps-api/products', [ApiProductsController::class, 'getProducts']);//->middleware('api-middleware');
});

// Sample route w/ sample middleware - please delete this

// Route::middleware('api-middleware')->group(function () {

//     Route::get('/sample-api', function() {

//         dump('Route service provider is working.');
//         dump('Route api route is working.');
//     });

// });
