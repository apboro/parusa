<?php

use App\Http\Controllers\API\Ships\Seats\SeatCategoriesEditController;
use App\Http\Controllers\API\Ships\ShipDeleteController;
use App\Http\Controllers\API\Ships\ShipEditController;
use App\Http\Controllers\API\Ships\ShipListController;
use App\Http\Controllers\API\Ships\ShipPropertiesController;
use App\Http\Controllers\API\Ships\ShipViewController;
use Illuminate\Support\Facades\Route;

Route::post('/ships', [ShipListController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);

Route::post('/ship/view', [ShipViewController::class, 'view'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/ship/properties', [ShipPropertiesController::class, 'properties'])->middleware(['allow:staff_admin']);

Route::post('/ship/get', [ShipEditController::class, 'get'])->middleware(['allow:staff_admin']);
Route::post('/ship/update', [ShipEditController::class, 'update'])->middleware(['allow:staff_admin']);

Route::post('/ship/delete', [ShipDeleteController::class, 'delete'])->middleware(['allow:staff_admin']);

Route::post('/ship/seat_categories/get', [SeatCategoriesEditController::class, 'get'])->middleware(['allow:staff_admin']);
Route::post('/ship/seat_categories/update', [SeatCategoriesEditController::class, 'update'])->middleware(['allow:staff_admin']);

