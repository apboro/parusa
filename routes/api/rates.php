<?php

use App\Http\Controllers\API\Rates\RateDeleteController;
use App\Http\Controllers\API\Rates\RatesListController;
use App\Http\Controllers\API\Rates\RateUpdateController;
use Illuminate\Support\Facades\Route;

Route::post('/rates', [RatesListController::class, 'list'])->middleware(['allow:staff_admin']);
Route::post('/rates/update', [RateUpdateController::class, 'update'])->middleware(['allow:staff_admin']);
Route::post('/rates/delete', [RateDeleteController::class, 'delete'])->middleware(['allow:staff_admin']);
