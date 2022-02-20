<?php

use App\Http\Controllers\API\Piers\PierDeleteController;
use App\Http\Controllers\API\Piers\PierEditController;
use App\Http\Controllers\API\Piers\PierPropertiesController;
use App\Http\Controllers\API\Piers\PiersListController;
use App\Http\Controllers\API\Piers\PierViewController;
use Illuminate\Support\Facades\Route;

Route::post('/piers', [PiersListController::class, 'list']);
Route::post('/piers/view', [PierViewController::class, 'view']);
Route::post('/piers/properties', [PierPropertiesController::class, 'properties']);

Route::post('/piers/get', [PierEditController::class, 'get']);
Route::post('/piers/update', [PierEditController::class, 'update']);

Route::post('/piers/delete', [PierDeleteController::class, 'delete'])->middleware('auth:sanctum');
