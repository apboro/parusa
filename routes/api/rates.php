<?php

use App\Http\Controllers\API\Rates\RateDeleteController;
use App\Http\Controllers\API\Rates\RateOverrideController;
use App\Http\Controllers\API\Rates\RatesListController;
use App\Http\Controllers\API\Rates\RateUpdateController;
use Illuminate\Support\Facades\Route;

Route::post('/rates', [RatesListController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,partner']);
Route::post('/rates/update', [RateUpdateController::class, 'update'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/rates/override', [RateOverrideController::class, 'override'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/rates/override_mass', [RateOverrideController::class, 'overrideMass'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/rates/override/form', [RateOverrideController::class, 'loadOverrideForm'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/rates/delete', [RateDeleteController::class, 'delete'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
