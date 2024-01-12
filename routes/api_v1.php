<?php

use App\Http\APIv1\Controllers\ApiExcursionsController;
use App\Http\APIv1\Controllers\ApiOrderConfirmController;
use App\Http\APIv1\Controllers\ApiOrderGetController;
use App\Http\APIv1\Controllers\ApiOrderReturnController;
use App\Http\APIv1\Controllers\ApiOrderStoreController;
use App\Http\APIv1\Controllers\ApiPiersController;
use App\Http\APIv1\Controllers\ApiShipsController;
use App\Http\APIv1\Controllers\ApiTripsController;

//all routes must have name with 'api.v1'
Route::middleware(['auth:sanctum', 'checkPartner'])->group(function (){
    Route::get('api/v1/excursions', ApiExcursionsController::class)->name('api.v1.excursions');
    Route::get('api/v1/trips', ApiTripsController::class)->name('api.v1.trips');
    Route::get('api/v1/piers', ApiPiersController::class)->name('api.v1.piers');
    Route::get('api/v1/ships', ApiShipsController::class)->name('api.v1.ships');
    Route::get('api/v1/orders', ApiOrderGetController::class)->name('api.v1.orders');
    Route::post('api/v1/order', ApiOrderStoreController::class)->name('api.v1.order.store');
    Route::patch('api/v1/order/confirm', ApiOrderConfirmController::class)->name('api.v1.order.confirm');
    Route::patch('api/v1/order/return', ApiOrderReturnController::class)->name('api.v1.order.return');
});
