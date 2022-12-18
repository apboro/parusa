<?php

use App\Http\Controllers\API\Statistics\StatisticsSalesTodayController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Statistics\StatisticsSalesController;

Route::post('/statistics/sales/list', [StatisticsSalesController::class, 'list'])->middleware(['allow:staff_admin']);
Route::post('/statistics/today/list', [StatisticsSalesTodayController::class, 'list'])->middleware(['allow:staff_admin']);
