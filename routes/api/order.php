<?php

use App\Http\Controllers\API\Order\PartnerMakeOrderController;
use App\Http\Controllers\API\Order\TerminalCurrentOrderController;
use App\Http\Controllers\API\Order\TerminalMakeOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/order/partner/make', [PartnerMakeOrderController::class, 'make']);

Route::post('/order/terminal/make', [TerminalMakeOrderController::class, 'make']);
Route::post('/order/terminal/send', [TerminalCurrentOrderController::class, 'send']);
Route::post('/order/terminal/cancel', [TerminalCurrentOrderController::class, 'cancel']);
Route::post('/order/terminal/delete', [TerminalCurrentOrderController::class, 'delete']);
