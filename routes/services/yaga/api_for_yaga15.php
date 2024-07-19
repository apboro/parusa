<?php

use App\Services\YagaAPI15\YagaOrderApiController;
use App\Services\YagaAPI15\YagaScheduleApiController;

Route::middleware(['auth:sanctum'])->prefix('api/yaga15/')->group(function (){
    Route::get('manifest', [YagaScheduleApiController::class,'getManifest']);
    Route::get('cities', [YagaScheduleApiController::class,'getCities']);
    Route::get('events', [YagaScheduleApiController::class,'getEvents']);
    Route::get('hallplan', [YagaScheduleApiController::class,'getHallplan']);
    Route::get('halls', [YagaScheduleApiController::class,'getHalls']);
    Route::get('organizers', [YagaScheduleApiController::class,'getOrganizers']);
    Route::get('persons', [YagaScheduleApiController::class,'getPersons']);
    Route::get('schedule', [YagaScheduleApiController::class,'getSchedule']);
    Route::get('venues', [YagaScheduleApiController::class,'getVenues']);


    Route::get('available-seats', [YagaOrderApiController::class,'availableSeats']);
    Route::post('reserve', [YagaOrderApiController::class,'reserve']);
    Route::post('approve', [YagaOrderApiController::class,'approve']);
    Route::post('order-info', [YagaOrderApiController::class,'orderInfo']);
    Route::post('order-status', [YagaOrderApiController::class,'orderStatus']);
    Route::post('cancel-order', [YagaOrderApiController::class,'cancelOrder']);
    Route::post('clear-reservation', [YagaOrderApiController::class,'clearReservation']);
    Route::post('approve', [YagaOrderApiController::class,'approve']);
});

