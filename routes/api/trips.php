<?php

use App\Http\Controllers\API\Trips\TripChainInfoController;
use App\Http\Controllers\API\Trips\TripDeleteController;
use App\Http\Controllers\API\Trips\TripEditController;
use App\Http\Controllers\API\Trips\TripPropertiesController;
use App\Http\Controllers\API\Trips\TripsListController;
use App\Http\Controllers\API\Trips\TripsSelectListController;
use App\Http\Controllers\API\Trips\TripViewController;
use Illuminate\Support\Facades\Route;

Route::post('/trips', [TripsListController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant']);

Route::post('/trips/get', [TripEditController::class, 'get'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/trips/info', [TripChainInfoController::class, 'info'])->middleware(['allow:staff_admin']);
Route::post('/trips/update', [TripEditController::class, 'update'])->middleware(['allow:staff_admin,staff_office_manager']);

Route::post('/trips/delete', [TripDeleteController::class, 'delete'])->middleware(['allow:staff_admin,staff_office_manager']);

Route::post('/trips/view', [TripViewController::class, 'view'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant']);
Route::post('/trips/properties', [TripPropertiesController::class, 'properties'])->middleware(['allow:staff_admin']);

Route::post('/trips/select', [TripsSelectListController::class, 'list'])->middleware(['allow:staff_terminal,partner']);
