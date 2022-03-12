<?php

use App\Http\Controllers\Services\LifePos\LifePosNotificationsController;
use Illuminate\Support\Facades\Route;

Route::post('/lifepos/notifications', [LifePosNotificationsController::class, 'lifePosNotification'])->name('lifePosNotification');
