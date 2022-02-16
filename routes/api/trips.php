<?php

use App\Http\Controllers\API\Trips\TripChainInfoController;
use App\Http\Controllers\API\Trips\TripDeleteController;
use App\Http\Controllers\API\Trips\TripEditController;
use App\Http\Controllers\API\Trips\TripsListController;
use App\Http\Controllers\API\Trips\TripViewController;
use Illuminate\Support\Facades\Route;

Route::post('/trips', [TripsListController::class, 'list']);

Route::post('/trips/get', [TripEditController::class, 'get']);
Route::post('/trips/info', [TripChainInfoController::class, 'info']);
Route::post('/trips/update', [TripEditController::class, 'update']);

Route::post('/trips/delete', [TripDeleteController::class, 'delete']);

Route::post('/trips/view', [TripViewController::class, 'view']);


//Route::post('/trips/status', [TripStatusController::class, 'setStatus'])->middleware('auth:sanctum');
//Route::post('/trips/sale-status', [TripStatusController::class, 'setSaleStatus'])->middleware('auth:sanctum');
//Route::post('/trips/discount-status', [TripStatusController::class, 'setDiscountStatus'])->middleware('auth:sanctum');
//Route::post('/trips/tickets-count', [TripDetailController::class, 'setTicketsCount'])->middleware('auth:sanctum');
//Route::post('/trips/cancellation-time', [TripDetailController::class, 'setCancellationTime'])->middleware('auth:sanctum');
