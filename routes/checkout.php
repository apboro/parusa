<?php

use App\Http\Controllers\Checkout\CheckoutInitController;
use Illuminate\Support\Facades\Route;

Route::prefix('/checkout')->middleware(['checkout'])->group(function () {
    Route::post('/init', [CheckoutInitController::class, 'init']);
//    Route::post('/order', [ShowcaseOrderController::class, 'order'])->middleware(['external.protect']);
});
