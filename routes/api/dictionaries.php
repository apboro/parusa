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

Route::post('/dictionaries', [DictionaryController::class, 'getDictionary']);

Route::post('/dictionaries/index', [DictionaryEditController::class, 'index']);
Route::post('/dictionaries/details', [DictionaryEditController::class, 'details']);
Route::post('/dictionaries/sync', [DictionaryEditController::class, 'sync']);
Route::post('/dictionaries/update', [DictionaryEditController::class, 'update']);
Route::post('/dictionaries/delete', [DictionaryDeleteController::class, 'delete']);
