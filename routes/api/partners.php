<?php

use App\Http\Controllers\API\Partners\PartnersListController;
use App\Http\Controllers\API\Partners\RepresentativeListController;
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

Route::post('/partners', [PartnersListController::class, 'list'])->middleware('auth:sanctum');

Route::post('/representatives', [RepresentativeListController::class, 'list'])->middleware('auth:sanctum');
