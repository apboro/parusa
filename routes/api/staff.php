<?php

use App\Http\Controllers\API\Staff\StaffAccessController;
use App\Http\Controllers\API\Staff\StaffDeleteController;
use App\Http\Controllers\API\Staff\StaffEditController;
use App\Http\Controllers\API\Staff\StaffListController;
use App\Http\Controllers\API\Staff\StaffPropertiesController;
use App\Http\Controllers\API\Staff\StaffViewController;
use Illuminate\Support\Facades\Route;

Route::post('/staff', [StaffListController::class, 'list']);

Route::post('/staff/view', [StaffViewController::class, 'view']);
Route::post('/staff/properties', [StaffPropertiesController::class, 'properties']);
Route::post('/staff/access/set', [StaffAccessController::class, 'set']);
Route::post('/staff/access/release', [StaffAccessController::class, 'release']);

Route::post('/staff/get', [StaffEditController::class, 'get']);
Route::post('/staff/update', [StaffEditController::class, 'update']);

Route::post('/staff/delete', [StaffDeleteController::class, 'delete']);
