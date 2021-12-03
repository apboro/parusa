<?php

use App\Http\Controllers\API\Users\StaffCardController;
use App\Http\Controllers\API\Users\StaffEditController;
use App\Http\Controllers\API\Users\StaffListController;
use App\Http\Controllers\API\Users\UsersListController;
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

Route::post('/users/staff', [StaffListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/users/staff/view', [StaffCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/users/staff/edit', [StaffEditController::class, 'get'])->middleware('auth:sanctum');


Route::post('/users/list', [UsersListController::class, 'list'])->middleware('auth:sanctum');
