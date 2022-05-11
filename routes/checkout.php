<?php

use App\Http\Controllers\Checkout\CheckoutController;
use App\Http\Controllers\Checkout\CheckoutInfoController;
use App\Http\Controllers\Checkout\CheckoutInitPayController;
use App\Http\Controllers\Checkout\CheckoutOrderController;
use Illuminate\Support\Facades\Route;

Route::prefix('/checkout')->middleware(['checkout'])->group(function () {
    Route::post('/handle', [CheckoutController::class, 'handle']);
    Route::post('/cancel', [CheckoutOrderController::class, 'cancel']);
    Route::post('/pay', [CheckoutInitPayController::class, 'pay']);
    Route::post('/excursion', [CheckoutInfoController::class, 'excursion']);
    Route::post('/pier', [CheckoutInfoController::class, 'pier']);
});
