<?php

use App\Http\Controllers\API\Settings\SettingsController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::post('/settings/general/get', [SettingsController::class, 'getGeneral'])->middleware('auth:sanctum');
Route::post('/settings/general/set', [SettingsController::class, 'setGeneral'])->middleware('auth:sanctum');
