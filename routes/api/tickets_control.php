<?php

use App\Http\Controllers\API\TicketsControl\TicketQrCodeCheckController;
use Illuminate\Support\Facades\Route;

Route::post('/ticket/qrcode/check',[TicketQrCodeCheckController::class, 'getScanData'])->middleware(['allow:staff_admin,staff_controller']);
Route::post('/ticket/qrcode/use',[TicketQrCodeCheckController::class, 'useTicket'])->middleware(['allow:staff_admin,staff_controller']);
