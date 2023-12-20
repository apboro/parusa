<?php

use App\Http\APIv1\Controllers\ApiExcursionsController;
use App\Http\APIv1\Controllers\ApiPiersController;
use App\Http\APIv1\Controllers\ApiTripsController;
use App\Http\Controllers\ApiShipsController;

Route::middleware('auth:sanctum')->group(function (){

    Route::get('api/v1/excursions', ApiExcursionsController::class);
    Route::get('api/v1/trips', ApiTripsController::class);
    Route::get('api/v1/piers', ApiPiersController::class);
    Route::get('api/v1/ships', ApiShipsController::class);

});
