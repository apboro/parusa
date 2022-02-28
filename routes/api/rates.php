<?php

use App\Http\Controllers\API\Rates\RateDeleteController;
use App\Http\Controllers\API\Rates\RatesListController;
use App\Http\Controllers\API\Rates\RateUpdateController;
use Illuminate\Support\Facades\Route;

Route::post('/rates', [RatesListController::class, 'list']);
Route::post('/rates/update', [RateUpdateController::class, 'update']);
Route::post('/rates/delete', [RateDeleteController::class, 'delete']);
