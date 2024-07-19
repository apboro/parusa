<?php

use App\Http\Controllers\API\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/settings/general/get', [SettingsController::class, 'getGeneral'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/settings/general/set', [SettingsController::class, 'setGeneral'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/settings/yaga/get', [SettingsController::class, 'getYaga'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/settings/yaga/set', [SettingsController::class, 'setYaga'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
