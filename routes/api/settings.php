<?php

use App\Http\Controllers\API\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

Route::post('/settings/general/get', [SettingsController::class, 'getGeneral'])->middleware('allow:staff_admin');
Route::post('/settings/general/set', [SettingsController::class, 'setGeneral'])->middleware('allow:staff_admin');
