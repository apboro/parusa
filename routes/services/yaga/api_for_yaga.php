<?php

use App\Services\YagaAPI\YagaOrderApiController;
use App\Services\YagaAPI\YagaScheduleApiController;

Route::middleware(['auth:sanctum'])->prefix('api/yaga/')->group(function (){
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
    Route::get('order-info', [YagaOrderApiController::class,'orderInfo']);
    Route::get('order-status', [YagaOrderApiController::class,'orderStatus']);
    Route::get('cancel-order', [YagaOrderApiController::class,'cancelOrder']);
    Route::get('check-promocode', [YagaOrderApiController::class,'checkPromocode']);
    Route::get('clear-reservation', [YagaOrderApiController::class,'clearReservation']);
    Route::get('approve', [YagaOrderApiController::class,'approve']);


});

