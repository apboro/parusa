<?php

use App\Http\APIv1\Controllers\ApiExcursionsController;
use App\Http\APIv1\Controllers\ApiOrderConfirmController;
use App\Http\APIv1\Controllers\ApiOrderGetController;
use App\Http\APIv1\Controllers\ApiOrderReturnController;
use App\Http\APIv1\Controllers\ApiOrderStoreController;
use App\Http\APIv1\Controllers\ApiPiersController;
use App\Http\APIv1\Controllers\ApiShipsController;
use App\Http\APIv1\Controllers\ApiTripsController;

Route::middleware(['auth:sanctum', 'checkPartner'])->group(function (){
    Route::get('api/v1/excursions', ApiExcursionsController::class);
    Route::get('api/v1/trips', ApiTripsController::class);
    Route::get('api/v1/piers', ApiPiersController::class);
    Route::get('api/v1/ships', ApiShipsController::class);
    Route::get('api/v1/orders', ApiOrderGetController::class);
    Route::post('api/v1/order', ApiOrderStoreController::class);
    Route::patch('api/v1/order/confirm', ApiOrderConfirmController::class);
    Route::patch('api/v1/order/return', ApiOrderReturnController::class);
});
