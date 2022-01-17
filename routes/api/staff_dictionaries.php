<?php

use App\Http\Controllers\API\Dictionary\DictionaryController;
use App\Http\Controllers\API\Dictionary\DictionaryDeleteController;
use App\Http\Controllers\API\Dictionary\DictionaryEditController;
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

Route::post('/dictionaries', [DictionaryController::class, 'getDictionary'])->middleware('auth:sanctum');

Route::post('/dictionaries/index', [DictionaryEditController::class, 'index'])->middleware('auth:sanctum');
Route::post('/dictionaries/details', [DictionaryEditController::class, 'details'])->middleware('auth:sanctum');
Route::post('/dictionaries/sync', [DictionaryEditController::class, 'sync'])->middleware('auth:sanctum');
Route::post('/dictionaries/update', [DictionaryEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/dictionaries/delete', [DictionaryDeleteController::class, 'delete'])->middleware('auth:sanctum');
