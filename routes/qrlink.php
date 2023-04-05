<?php

use App\Http\Controllers\QrCode\QrCodeRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/qrlink/{hash}', [QrCodeRedirectController::class, 'redirect'])->name('qrlink')->middleware(['checkout']);

