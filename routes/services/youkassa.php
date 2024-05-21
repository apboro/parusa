<?php

use App\Services\YouKassa\YouKassaNotificationsController;
use Illuminate\Support\Facades\Route;

Route::post('/youkassa/notifications', [YouKassaNotificationsController::class])->name('youkassaNotification');
