<?php

use App\Http\Controllers\API\Staff\StaffAccessController;
use App\Http\Controllers\API\Staff\StaffDeleteController;
use App\Http\Controllers\API\Staff\StaffEditController;
use App\Http\Controllers\API\Staff\StaffListController;
use App\Http\Controllers\API\Staff\StaffPropertiesController;
use App\Http\Controllers\API\Staff\StaffViewController;
use Illuminate\Support\Facades\Route;

Route::post('/staff', [StaffListController::class, 'list'])->middleware(['allow:staff_admin']);

Route::post('/staff/view', [StaffViewController::class, 'view'])->middleware(['allow:staff_admin']);
Route::post('/staff/properties', [StaffPropertiesController::class, 'properties'])->middleware(['allow:staff_admin']);
Route::post('/staff/access/set', [StaffAccessController::class, 'set'])->middleware(['allow:staff_admin']);
Route::post('/staff/access/release', [StaffAccessController::class, 'release'])->middleware(['allow:staff_admin']);

Route::post('/company/staff/get', [StaffEditController::class, 'get'])->middleware(['allow:staff_admin']);
Route::post('/company/staff/update', [StaffEditController::class, 'update'])->middleware(['allow:staff_admin']);

Route::post('/staff/delete', [StaffDeleteController::class, 'delete'])->middleware(['allow:staff_admin']);
