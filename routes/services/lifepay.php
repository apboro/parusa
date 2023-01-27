<?php

use App\Http\Controllers\Services\LifePay\CloudPrintNotificationsController;
use App\Http\Controllers\Services\LifePay\LifePayNotificationsController;
use Illuminate\Support\Facades\Route;

Route::post('/lifepay/notifications', [LifePayNotificationsController::class, 'lifePayNotification'])->name('lifePayNotification');
Route::post('/cloud_print/notifications', [CloudPrintNotificationsController::class, 'cloudPrintNotification'])->name('cloudPrintNotification');
