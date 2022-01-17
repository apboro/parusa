<?php

use App\Http\Controllers\API\Company\StaffAccessController;
use App\Http\Controllers\API\Company\StaffCardController;
use App\Http\Controllers\API\Company\StaffDeleteController;
use App\Http\Controllers\API\Company\StaffEditController;
use App\Http\Controllers\API\Company\StaffListController;
use App\Http\Controllers\API\Company\StaffStatusController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/company/staff', [StaffListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/company/staff/view', [StaffCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/company/staff/get', [StaffEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/company/staff/update', [StaffEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/company/staff/status', [StaffStatusController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/company/staff/delete', [StaffDeleteController::class, 'delete'])->middleware('auth:sanctum');
Route::post('/company/staff/access/set', [StaffAccessController::class, 'set'])->middleware('auth:sanctum');
Route::post('/company/staff/access/release', [StaffAccessController::class, 'release'])->middleware('auth:sanctum');

