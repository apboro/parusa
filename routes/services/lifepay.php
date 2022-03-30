<?php

use App\Http\Controllers\Services\LifePay\LifePayNotificationsController;
use Illuminate\Support\Facades\Route;

Route::post('/lifepay/notifications', [LifePayNotificationsController::class, 'lifePayNotification'])->name('lifePayNotification');
