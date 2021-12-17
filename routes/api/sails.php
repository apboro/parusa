<?php

use App\Http\Controllers\API\Sails\ExcursionCardController;
use App\Http\Controllers\API\Sails\ExcursionDeleteController;
use App\Http\Controllers\API\Sails\ExcursionEditController;
use App\Http\Controllers\API\Sails\ExcursionsListController;
use App\Http\Controllers\API\Sails\PierCardController;
use App\Http\Controllers\API\Sails\PierDeleteController;
use App\Http\Controllers\API\Sails\PierEditController;
use App\Http\Controllers\API\Sails\PiersListController;
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

Route::post('/piers', [PiersListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/piers/view', [PierCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/piers/get', [PierEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/piers/update', [PierEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/piers/status', [PierEditController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/piers/delete', [PierDeleteController::class, 'delete'])->middleware('auth:sanctum');

Route::post('/excursions', [ExcursionsListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/excursions/view', [ExcursionCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/excursions/get', [ExcursionEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/excursions/status', [ExcursionEditController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/excursions/update', [ExcursionEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/excursions/delete', [ExcursionDeleteController::class, 'delete'])->middleware('auth:sanctum');

