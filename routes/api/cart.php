<?php

use App\Http\Controllers\API\Cart\CartController;
use Illuminate\Support\Facades\Route;

Route::post('/cart', [CartController::class, 'get'])->middleware(['allow:staff_terminal,partner']);
Route::post('/cart/add', [CartController::class, 'add'])->middleware(['allow:staff_terminal,partner']);
Route::post('/cart/remove', [CartController::class, 'remove'])->middleware(['allow:staff_terminal,partner']);
