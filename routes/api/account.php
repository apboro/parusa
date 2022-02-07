<?php

use App\Http\Controllers\API\Account\AccountLimitController;
use App\Http\Controllers\API\Account\AccountRefillController;
use App\Http\Controllers\API\Account\TransactionDeleteController;
use App\Http\Controllers\API\Account\TransactionsListController;
use App\Http\Controllers\API\Order\OrderCardController;
use App\Http\Controllers\API\Order\OrderController;
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

Route::post('/account', [TransactionsListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/account/limit', [AccountLimitController::class, 'setAccountLimit'])->middleware('auth:sanctum');
Route::post('/account/refill', [AccountRefillController::class, 'refill'])->middleware('auth:sanctum');
Route::post('/account/delete', [TransactionDeleteController::class, 'delete'])->middleware('auth:sanctum');

Route::post('/order/get', [OrderController::class, 'get'])->middleware('auth:sanctum');
Route::post('/order/remove', [OrderController::class, 'remove'])->middleware('auth:sanctum');
Route::post('/order/make', [OrderController::class, 'make'])->middleware('auth:sanctum');
Route::post('/order/info', [OrderCardController::class, 'get'])->middleware('auth:sanctum');
