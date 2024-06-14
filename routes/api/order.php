<?php

use App\Http\Controllers\API\Order\OrderBackwardTicketsController;
use App\Http\Controllers\API\Order\OrderInstantPayController;
use App\Http\Controllers\API\Order\OrderReserveController;
use App\Http\Controllers\API\Order\OrderReturnController;
use App\Http\Controllers\API\Order\OrderSMSController;
use App\Http\Controllers\API\Order\OrderTicketReplacementController;
use App\Http\Controllers\API\Order\PartnerMakeOrderController;
use App\Http\Controllers\API\Order\PromoterMakeOrderController;
use App\Http\Controllers\API\Order\TerminalCurrentOrderController;
use App\Http\Controllers\API\Order\TerminalMakeOrderController;
use App\Http\Controllers\QrCode\QrCodeGenerateController;
use Illuminate\Support\Facades\Route;

Route::post('/order/partner/make', [PartnerMakeOrderController::class, 'make'])->middleware(['allow:partner']);

Route::post('/order/promoter/make', [PromoterMakeOrderController::class, 'make'])->middleware(['allow:partner']);

Route::post('/order/terminal/make', [TerminalMakeOrderController::class, 'make'])->middleware(['allow:staff_terminal']);

Route::post('/order/terminal/send', [TerminalCurrentOrderController::class, 'send'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/cancel', [TerminalCurrentOrderController::class, 'cancel'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/delete', [TerminalCurrentOrderController::class, 'delete'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/status', [TerminalCurrentOrderController::class, 'status'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/close', [TerminalCurrentOrderController::class, 'close'])->middleware(['allow:staff_terminal']);
Route::post('/order/terminal/save_unconfirmed', [TerminalCurrentOrderController::class, 'saveUnconfirmed'])->middleware(['allow:staff_terminal']);

Route::post('/order/send_sms', [OrderSMSController::class, 'sendSMS'])->middleware(['allow:staff_terminal,staff_admin,partner']);
Route::post('/order/send_payment_link_sms', [OrderSMSController::class, 'sendPaymentLinkSMS'])->middleware(['allow:staff_admin,partner']);

Route::post('/order/return', [OrderReturnController::class, 'return'])->middleware(['allow:staff_admin,staff_promoter_manager']);

Route::post('/order/reserve/remove', [OrderReserveController::class, 'remove']);
Route::post('/order/reserve/cancel', [OrderReserveController::class, 'cancel']);

Route::post('/order/reserve/order', [OrderReserveController::class, 'partnerOrder'])->middleware(['allow:partner']);
Route::post('/order/reserve/accept', [OrderReserveController::class, 'terminalOrder'])->middleware(['allow:staff_terminal']);

Route::post('/order/replacement/get_available_dates', [OrderTicketReplacementController::class, 'getAvailableDates'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_terminal']);
Route::post('/order/replacement/get_trips_for_date', [OrderTicketReplacementController::class, 'getTripsForDate'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_terminal']);
Route::post('/order/replacement/make', [OrderTicketReplacementController::class, 'replaceTickets'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_terminal']);

Route::post('/order/backward/get_backward_trips', [OrderBackwardTicketsController::class, 'getBackwardTrips'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_terminal,partner']);
Route::post('/order/backward/add_backward_tickets', [OrderBackwardTicketsController::class, 'addBackwardTickets'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_terminal,partner']);
Route::post('/order/backward/add_backward_tickets_showcase', [OrderBackwardTicketsController::class, 'addBackwardTicketsShowcase'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_terminal,partner']);

Route::post('/order/generate_payment_code', [QrCodeGenerateController::class, 'generateOrderPaymentQr'])->middleware(['allow:staff_admin,partner']);

Route::post('/order/instant_pay', [OrderInstantPayController::class, 'pay'])->middleware(['allow:partner']);
