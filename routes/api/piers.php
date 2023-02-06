<?php

use App\Http\Controllers\API\Piers\PierDeleteController;
use App\Http\Controllers\API\Piers\PierEditController;
use App\Http\Controllers\API\Piers\PierInfoController;
use App\Http\Controllers\API\Piers\PierPropertiesController;
use App\Http\Controllers\API\Piers\PiersListController;
use App\Http\Controllers\API\Piers\PierViewController;
use Illuminate\Support\Facades\Route;

Route::post('/piers', [PiersListController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant']);
Route::post('/piers/view', [PierViewController::class, 'view'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant']);
Route::post('/piers/properties', [PierPropertiesController::class, 'properties'])->middleware(['allow:staff_admin,staff_office_manager']);

Route::post('/piers/get', [PierEditController::class, 'get'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/piers/update', [PierEditController::class, 'update'])->middleware(['allow:staff_admin,staff_office_manager']);

Route::post('/piers/delete', [PierDeleteController::class, 'delete'])->middleware(['allow:staff_admin,staff_office_manager']);

Route::post('/piers/info', [PierInfoController::class, 'info'])->middleware(['allow:partner']);
