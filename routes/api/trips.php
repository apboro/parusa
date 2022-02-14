<?php

use App\Http\Controllers\API\Trips\TripChainInfoController;
use App\Http\Controllers\API\Trips\TripDeleteController;
use App\Http\Controllers\API\Trips\TripEditController;
use App\Http\Controllers\API\Trips\TripsListController;
use Illuminate\Support\Facades\Route;

Route::post('/trips', [TripsListController::class, 'list']);
Route::post('/trips/get', [TripEditController::class, 'get']);
Route::post('/trips/update', [TripEditController::class, 'update']);
Route::post('/trips/delete', [TripDeleteController::class, 'delete']);
Route::post('/trips/chain', [TripChainInfoController::class, 'info']);
