<?php

use App\Http\Controllers\API\Cart\PartnerCartController;
use Illuminate\Support\Facades\Route;

Route::post('/cart', [PartnerCartController::class, 'get']);
Route::post('/cart/add', [PartnerCartController::class, 'add']);
Route::post('/cart/remove', [PartnerCartController::class, 'remove']);
