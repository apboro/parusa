<?php

use App\Http\Controllers\API\Users\UsersListController;
use Illuminate\Http\Request;
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

Route::post('/users/staff', [UsersListController::class, 'staffList'])->middleware('auth:sanctum');
Route::post('/users/list', [UsersListController::class, 'usersList'])->middleware('auth:sanctum');
