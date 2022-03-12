<?php

use App\Http\Controllers\API\Piers\PierDeleteController;
use App\Http\Controllers\API\Piers\PierEditController;
use App\Http\Controllers\API\Piers\PierPropertiesController;
use App\Http\Controllers\API\Piers\PiersListController;
use App\Http\Controllers\API\Piers\PierViewController;
use Illuminate\Support\Facades\Route;

Route::post('/piers', [PiersListController::class, 'list'])->middleware(['allow:staff_admin']);
Route::post('/piers/view', [PierViewController::class, 'view'])->middleware(['allow:staff_admin']);
Route::post('/piers/properties', [PierPropertiesController::class, 'properties'])->middleware(['allow:staff_admin']);

Route::post('/piers/get', [PierEditController::class, 'get'])->middleware(['allow:staff_admin']);
Route::post('/piers/update', [PierEditController::class, 'update'])->middleware(['allow:staff_admin']);

Route::post('/piers/delete', [PierDeleteController::class, 'delete'])->middleware(['allow:staff_admin']);
