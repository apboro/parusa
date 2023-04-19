<?php

use App\Http\Controllers\API\Order\OrderReserveController;
use App\Http\Controllers\API\Order\OrderReturnController;
use App\Http\Controllers\API\Order\OrderTransferController;
use App\Http\Controllers\API\Order\PartnerMakeOrderController;
use App\Http\Controllers\API\Order\TerminalCurrentOrderController;
use App\Http\Controllers\API\Order\TerminalMakeOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/order/partner/make', [PartnerMakeOrderController::class, 'make'])->middleware(['allow:partner']);

Route::post('/order/terminal/make', [TerminalMakeOrderController::class, 'make'])->middleware(['allow:staff_terminal']);

Route::post('/order/terminal/send', [TerminalCurrentOrderController::class, 'send'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/cancel', [TerminalCurrentOrderController::class, 'cancel'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/delete', [TerminalCurrentOrderController::class, 'delete'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/status', [TerminalCurrentOrderController::class, 'status'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/close', [TerminalCurrentOrderController::class, 'close'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/save_unconfirmed', [TerminalCurrentOrderController::class, 'saveUnconfirmed'])->middleware(['allow:staff_terminal']);

Route::post('/order/return', [OrderReturnController::class, 'return']);

Route::post('/order/reserve/remove', [OrderReserveController::class, 'remove']);
Route::post('/order/reserve/cancel', [OrderReserveController::class, 'cancel']);

Route::post('/order/reserve/order', [OrderReserveController::class, 'partnerOrder'])->middleware(['allow:partner']);
Route::post('/order/reserve/accept', [OrderReserveController::class, 'terminalOrder'])->middleware(['allow:staff_terminal']);

Route::post('/order/transfer', [OrderTransferController::class, 'transfer'])->middleware(['allow:staff_terminal']);
Route::post('/order/transfer/update', [OrderTransferController::class, 'update'])->middleware(['allow:staff_terminal']);
Route::post('/order/transfer/trips', [OrderTransferController::class, 'trips'])->middleware(['allow:staff_terminal']);
