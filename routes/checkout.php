<?php

use App\Http\Controllers\Checkout\CheckoutController;
use Illuminate\Support\Facades\Route;

Route::prefix('/checkout')->middleware(['checkout'])->group(function () {
    Route::post('/handle', [CheckoutController::class, 'handle']);
    Route::post('/pay', [CheckoutController::class, 'pay']);
});
