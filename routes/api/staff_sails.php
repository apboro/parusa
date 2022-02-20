<?php

use App\Http\Controllers\API\Sails\ExcursionCardController;
use App\Http\Controllers\API\Sails\ExcursionDeleteController;
use App\Http\Controllers\API\Sails\ExcursionEditController;
use App\Http\Controllers\API\Sails\ExcursionStatusController;
use App\Http\Controllers\API\Sails\TripsSelectListController;
use App\Http\Controllers\API\Tickets\ExcursionRatesController;
use App\Http\Controllers\API\Tickets\ExcursionRatesDeleteController;
use App\Http\Controllers\API\Tickets\TripTicketsAddController;
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




Route::post('/excursions/view', [ExcursionCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/excursions/get', [ExcursionEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/excursions/update', [ExcursionEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/excursions/status', [ExcursionStatusController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/excursions/delete', [ExcursionDeleteController::class, 'delete'])->middleware('auth:sanctum');
Route::post('/excursions/rates', [ExcursionRatesController::class, 'get'])->middleware('auth:sanctum');
Route::post('/excursions/rates/update', [ExcursionRatesController::class, 'update'])->middleware('auth:sanctum');
Route::post('/excursions/rates/delete', [ExcursionRatesDeleteController::class, 'delete'])->middleware('auth:sanctum');


Route::post('/trips/select', [TripsSelectListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/trips/add_tickets_to_cart', [TripTicketsAddController::class, 'add'])->middleware('auth:sanctum');
