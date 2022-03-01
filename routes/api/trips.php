<?php

use App\Http\Controllers\API\Trips\TripChainInfoController;
use App\Http\Controllers\API\Trips\TripDeleteController;
use App\Http\Controllers\API\Trips\TripEditController;
use App\Http\Controllers\API\Trips\TripPropertiesController;
use App\Http\Controllers\API\Trips\TripsListController;
use App\Http\Controllers\API\Trips\TripsSelectListController;
use App\Http\Controllers\API\Trips\TripViewController;
use Illuminate\Support\Facades\Route;

Route::post('/trips', [TripsListController::class, 'list']);
Route::post('/trips/select', [TripsSelectListController::class, 'list']);

Route::post('/trips/get', [TripEditController::class, 'get']);
Route::post('/trips/info', [TripChainInfoController::class, 'info']);
Route::post('/trips/update', [TripEditController::class, 'update']);

Route::post('/trips/delete', [TripDeleteController::class, 'delete']);

Route::post('/trips/view', [TripViewController::class, 'view']);

Route::post('/trips/properties', [TripPropertiesController::class, 'properties']);
