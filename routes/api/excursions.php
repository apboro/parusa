<?php

use App\Http\Controllers\API\Excursions\ExcursionDeleteController;
use App\Http\Controllers\API\Excursions\ExcursionEditController;
use App\Http\Controllers\API\Excursions\ExcursionPropertiesController;
use App\Http\Controllers\API\Excursions\ExcursionsListController;
use App\Http\Controllers\API\Excursions\ExcursionViewController;
use Illuminate\Support\Facades\Route;

Route::post('/excursions', [ExcursionsListController::class, 'list'])->middleware(['allow:staff_admin']);

Route::post('/excursions/view', [ExcursionViewController::class, 'view'])->middleware(['allow:staff_admin']);
Route::post('/excursions/properties', [ExcursionPropertiesController::class, 'properties'])->middleware(['allow:staff_admin']);

Route::post('/excursions/get', [ExcursionEditController::class, 'get'])->middleware(['allow:staff_admin']);
Route::post('/excursions/update', [ExcursionEditController::class, 'update'])->middleware(['allow:staff_admin']);

Route::post('/excursions/delete', [ExcursionDeleteController::class, 'delete'])->middleware(['allow:staff_admin']);
