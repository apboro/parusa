<?php

use App\Http\Controllers\API\Order\OrderBackwardTicketsController;
use App\Http\Controllers\Showcase\ShowcaseInfoController;
use App\Http\Controllers\Showcase\ShowcaseInitController;
use App\Http\Controllers\Showcase\ShowcaseOrderController;
use App\Http\Controllers\Showcase\ShowcaseOrderInfoController;
use App\Http\Controllers\Showcase\ShowcasePromoCodeController;
use App\Http\Controllers\Showcase\ShowcaseTripsController;
use App\Http\Controllers\ShowcaseV2\ShowcaseV2InfoController;
use App\Http\Controllers\ShowcaseV2\ShowcaseV2InitController;
use App\Http\Controllers\ShowcaseV2\ShowcaseV2OrderController;
use App\Http\Controllers\ShowcaseV2\ShowcaseV2OrderInfoController;
use App\Http\Controllers\ShowcaseV2\ShowcaseV2PromoCodeController;
use App\Http\Controllers\ShowcaseV2\ShowcaseV2TripsController;
use Illuminate\Support\Facades\Route;

Route::prefix('/showcase')->middleware(['showcase'])->group(function () {

    Route::post('/init', [ShowcaseInitController::class, 'init']);
    Route::post('/trips', [ShowcaseTripsController::class, 'trips'])->middleware(['external.protect']);
    Route::post('/trip', [ShowcaseTripsController::class, 'trip'])->middleware(['external.protect']);
    Route::post('/excursion', [ShowcaseInfoController::class, 'excursion'])->middleware(['external.protect']);
    Route::post('/pier', [ShowcaseInfoController::class, 'pier'])->middleware(['external.protect']);
    Route::post('/get_backward_trips', [ShowcaseTripsController::class, 'getBackwardTrips']);
    Route::post('/order', [ShowcaseOrderController::class, 'order'])->middleware(['external.protect']);
    Route::post('/order/cancel', [ShowcaseOrderController::class, 'cancel'])->middleware(['external.protect']);
    Route::post('/order/info', [ShowcaseOrderInfoController::class, 'info'])->middleware(['external.protect']);

    Route::post('/promo-code/use', [ShowcasePromoCodeController::class, 'init'])->middleware(['external.protect']);
});

Route::prefix('/showcase_v2')->middleware(['showcase'])->group(function () {
    Route::post('/init', [ShowcaseV2InitController::class, 'init']);
    Route::post('/trips', [ShowcaseV2TripsController::class, 'trips'])->middleware(['external.protect']);
    Route::post('/trip', [ShowcaseV2TripsController::class, 'trip'])->middleware(['external.protect']);
    Route::post('/excursion', [ShowcaseV2InfoController::class, 'excursion'])->middleware(['external.protect']);
    Route::post('/pier', [ShowcaseV2InfoController::class, 'pier'])->middleware(['external.protect']);

    Route::post('/order', [ShowcaseV2OrderController::class, 'order'])->middleware(['external.protect']);
    Route::post('/order/cancel', [ShowcaseV2OrderController::class, 'cancel'])->middleware(['external.protect']);
    Route::post('/order/info', [ShowcaseV2OrderInfoController::class, 'info'])->middleware(['external.protect']);
    Route::post('/promo-code/use', [ShowcaseV2PromoCodeController::class, 'init'])->middleware(['external.protect']);
});
