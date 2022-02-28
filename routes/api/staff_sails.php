<?php

use App\Http\Controllers\API\Sails\ExcursionEditController;
use App\Http\Controllers\API\Sails\TripsSelectListController;
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




Route::post('/excursions/get', [ExcursionEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/excursions/update', [ExcursionEditController::class, 'update'])->middleware('auth:sanctum');





Route::post('/trips/select', [TripsSelectListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/trips/add_tickets_to_cart', [TripTicketsAddController::class, 'add'])->middleware('auth:sanctum');
