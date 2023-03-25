<?php

use App\Http\Controllers\API\Partners\PartnerSettingsController;
use App\Http\Controllers\Showcase\ShowcaseInfoController;
use App\Http\Controllers\Showcase\ShowcaseInitController;
use App\Http\Controllers\Showcase\ShowcaseOrderController;
use App\Http\Controllers\Showcase\ShowcaseOrderInfoController;
use App\Http\Controllers\Showcase\ShowcaseTripsController;
use Illuminate\Support\Facades\Route;

Route::prefix('/showcase')->middleware(['showcase'])->group(function () {

    Route::post('/init', [ShowcaseInitController::class, 'init']);
    Route::post('/trips', [ShowcaseTripsController::class, 'trips'])->middleware(['external.protect']);
    Route::post('/trip', [ShowcaseTripsController::class, 'trip'])->middleware(['external.protect']);
    Route::post('/excursion', [ShowcaseInfoController::class, 'excursion'])->middleware(['external.protect']);
    Route::post('/pier', [ShowcaseInfoController::class, 'pier'])->middleware(['external.protect']);

    Route::post('/order', [ShowcaseOrderController::class, 'order'])->middleware(['external.protect']);
    Route::post('/order/cancel', [ShowcaseOrderController::class, 'cancel'])->middleware(['external.protect']);
    Route::post('/order/info', [ShowcaseOrderInfoController::class, 'info'])->middleware(['external.protect']);
});
