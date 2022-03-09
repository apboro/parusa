<?php

use App\Http\Controllers\API\Order\PartnerMakeOrderController;
use App\Http\Controllers\API\Order\TerminalCurrentOrderController;
use App\Http\Controllers\API\Order\TerminalMakeOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/order/partner/make', [PartnerMakeOrderController::class, 'make']);

Route::post('/order/terminal/make', [TerminalMakeOrderController::class, 'make']);
Route::post('/order/terminal/current', [TerminalCurrentOrderController::class, 'current']);
