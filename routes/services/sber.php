<?php

use App\Http\Controllers\Services\Sber\SberNotificationsController;
use Illuminate\Support\Facades\Route;

Route::post('/sber/notifications', [SberNotificationsController::class, 'sberNotification'])->name('sberNotification');
