<?php

use App\Http\Controllers\API\Account\AccountLimitController;
use App\Http\Controllers\API\Account\AccountRefillController;
use App\Http\Controllers\API\Account\TransactionDeleteController;
use App\Http\Controllers\API\Account\TransactionsListController;
use App\Http\Controllers\API\Order\OrderCardController;
use App\Http\Controllers\API\Order\OrderController;
use App\Http\Controllers\API\Order\TicketCardController;
use Illuminate\Support\Facades\Route;

Route::post('/account', [TransactionsListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/account/limit', [AccountLimitController::class, 'setAccountLimit'])->middleware('auth:sanctum');
Route::post('/account/refill', [AccountRefillController::class, 'refill'])->middleware('auth:sanctum');
Route::post('/account/delete', [TransactionDeleteController::class, 'delete'])->middleware('auth:sanctum');

Route::post('/order/get', [OrderController::class, 'get'])->middleware('auth:sanctum');
Route::post('/order/remove', [OrderController::class, 'remove'])->middleware('auth:sanctum');
Route::post('/order/make', [OrderController::class, 'make'])->middleware('auth:sanctum');
Route::post('/order/info', [OrderCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/ticket/info', [TicketCardController::class, 'get'])->middleware('auth:sanctum');
