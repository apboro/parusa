<?php

use App\Http\APIv1\Controllers\ApiExcursionsController;
use App\Http\APIv1\Controllers\ApiTripsController;

Route::middleware('auth:sanctum')->group(function (){

    Route::get('api/v1/excursions', ApiExcursionsController::class);
    Route::get('api/v1/trips', ApiTripsController::class);

});
