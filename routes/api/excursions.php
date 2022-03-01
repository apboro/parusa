<?php

use App\Http\Controllers\API\Excursions\ExcursionDeleteController;
use App\Http\Controllers\API\Excursions\ExcursionsListController;
use App\Http\Controllers\API\Excursions\ExcursionPropertiesController;
use App\Http\Controllers\API\Excursions\ExcursionViewController;
use Illuminate\Support\Facades\Route;

Route::post('/excursions', [ExcursionsListController::class, 'list']);

Route::post('/excursions/view', [ExcursionViewController::class, 'view']);
Route::post('/excursions/properties', [ExcursionPropertiesController::class, 'properties']);

// TODO refactor
Route::post('/excursions/get', [\App\Http\Controllers\API\Sails\ExcursionEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/excursions/update', [\App\Http\Controllers\API\Sails\ExcursionEditController::class, 'update'])->middleware('auth:sanctum');

Route::post('/excursions/delete', [ExcursionDeleteController::class, 'delete']);
