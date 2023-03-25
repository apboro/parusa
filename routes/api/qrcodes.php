<?php

use App\Http\Controllers\QrCode\QrCodeCreateController;
use App\Http\Controllers\QrCode\QrCodeGenerateController;
use App\Http\Controllers\QrCode\QrCodeListController;
use Illuminate\Support\Facades\Route;

Route::post('/qrcodes/list', [QrCodeListController::class, 'getQrCodesList'])->middleware(['allow:partner']);
Route::post('/qrcodes/update_or_create', [QrCodeCreateController::class, 'updateOrCreate'])->middleware(['allow:partner']);
Route::post('/qrcodes/generate', [QrCodeGenerateController::class, 'generateQr'])->middleware(['allow:partner,staff_admin']);
