<?php

use App\Http\Controllers\QrCode\QrCodeCreateController;
use App\Http\Controllers\QrCode\QrCodeGenerateController;
use App\Http\Controllers\QrCode\QrCodeListController;
use Illuminate\Support\Facades\Route;

Route::post('/qrcodes/list', [QrCodeListController::class, 'getQrCodesList'])->middleware(['allow:staff_admin,partner']);
Route::post('/qrcodes/update_or_create', [QrCodeCreateController::class, 'updateOrCreate'])->middleware(['allow:staff_admin,partner']);
Route::post('/qrcodes/generate', [QrCodeGenerateController::class, 'generateQr'])->middleware(['allow:staff_admin,partner,staff_admin']);
