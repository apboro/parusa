<?php

use App\Http\Controllers\API\Account\AccountLimitController;
use App\Http\Controllers\API\Account\AccountRefillController;
use App\Http\Controllers\API\Account\TransactionDeleteController;
use App\Http\Controllers\API\Account\TransactionsListController;
use Illuminate\Support\Facades\Route;

Route::post('/account', [TransactionsListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/account/limit', [AccountLimitController::class, 'setAccountLimit'])->middleware('auth:sanctum');
Route::post('/account/refill', [AccountRefillController::class, 'refill'])->middleware('auth:sanctum');
Route::post('/account/delete', [TransactionDeleteController::class, 'delete'])->middleware('auth:sanctum');

