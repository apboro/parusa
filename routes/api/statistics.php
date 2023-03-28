<?php

use App\Http\Controllers\API\Statistics\StatisticsQrCodesController;
use App\Http\Controllers\API\Statistics\StatisticsSalesController;
use App\Http\Controllers\API\Statistics\StatisticsSalesTodayController;
use Illuminate\Support\Facades\Route;

Route::post('/statistics/sales/list', [StatisticsSalesController::class, 'list'])->middleware(['allow:staff_admin']);
Route::post('/statistics/today/list', [StatisticsSalesTodayController::class, 'list'])->middleware(['allow:staff_admin']);
Route::post('/statistics/qrcodes/list', [StatisticsQrCodesController::class, 'list'])->middleware(['allow:staff_admin']);
