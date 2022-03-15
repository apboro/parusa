<?php

use App\Http\Controllers\API\Order\OrderReserveController;
use App\Http\Controllers\API\Order\OrderReturnController;
use App\Http\Controllers\API\Order\PartnerMakeOrderController;
use App\Http\Controllers\API\Order\TerminalCurrentOrderController;
use App\Http\Controllers\API\Order\TerminalMakeOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/order/partner/make', [PartnerMakeOrderController::class, 'make']);

Route::post('/order/terminal/make', [TerminalMakeOrderController::class, 'make']);

Route::post('/order/terminal/send', [TerminalCurrentOrderController::class, 'send']);
Route::post('/order/terminal/cancel', [TerminalCurrentOrderController::class, 'cancel']);
Route::post('/order/terminal/delete', [TerminalCurrentOrderController::class, 'delete']);
Route::post('/order/terminal/status', [TerminalCurrentOrderController::class, 'status']);
Route::post('/order/terminal/close', [TerminalCurrentOrderController::class, 'close']);

Route::post('/order/return', [OrderReturnController::class, 'return']);

Route::post('/order/reserve/remove', [OrderReserveController::class, 'remove']);
Route::post('/order/reserve/cancel', [OrderReserveController::class, 'cancel']);
