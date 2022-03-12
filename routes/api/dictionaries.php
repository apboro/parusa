<?php

use App\Http\Controllers\API\Dictionary\DictionaryController;
use App\Http\Controllers\API\Dictionary\DictionaryDeleteController;
use App\Http\Controllers\API\Dictionary\DictionaryEditController;
use Illuminate\Support\Facades\Route;

Route::post('/dictionaries', [DictionaryController::class, 'getDictionary'])->middleware(['allow:staff_admin,staff_terminal,partner']);

Route::post('/dictionaries/index', [DictionaryEditController::class, 'index'])->middleware(['allow:staff_admin']);
Route::post('/dictionaries/details', [DictionaryEditController::class, 'details'])->middleware(['allow:staff_admin']);
Route::post('/dictionaries/sync', [DictionaryEditController::class, 'sync'])->middleware(['allow:staff_admin']);
Route::post('/dictionaries/update', [DictionaryEditController::class, 'update'])->middleware(['allow:staff_admin']);
Route::post('/dictionaries/delete', [DictionaryDeleteController::class, 'delete'])->middleware(['allow:staff_admin']);
