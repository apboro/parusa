<?php

use App\Http\Controllers\API\PromoCodes\PromoCodeEditController;
use App\Http\Controllers\API\PromoCodes\PromoCodeListController;
use Illuminate\Support\Facades\Route;

Route::post('/promo-codes', [PromoCodeListController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/promo-code/status', [PromoCodeEditController::class, 'status'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);

Route::post('/promo-code/get', [PromoCodeEditController::class, 'get'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/promo-code/update', [PromoCodeEditController::class, 'update'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
