<?php

use App\Http\Controllers\PartnerAPI\PartnerInfoController;
use App\Http\Controllers\PartnerAPI\PartnerSelfController;
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

Route::post('/partners/partner/view', [PartnerSelfController::class, 'get'])->middleware(['allow:partner']);
Route::post('/partners/partner/info', [PartnerInfoController::class, 'get'])->middleware(['allow:partner']);
