<?php

use App\Http\Controllers\API\Cart\PartnerCartController;
use App\Http\Controllers\API\Cart\PromoterCartController;
use App\Http\Controllers\API\Cart\TerminalCartController;
use App\Http\Controllers\SchemeCartController;
use Illuminate\Support\Facades\Route;

Route::post('/cart/partner', [PartnerCartController::class, 'get'])->middleware(['allow:partner']);
Route::post('/cart/partner/add', [PartnerCartController::class, 'add'])->middleware(['allow:partner']);
Route::post('/cart/partner/quantity', [PartnerCartController::class, 'quantity'])->middleware(['allow:partner']);
Route::post('/cart/partner/remove', [PartnerCartController::class, 'remove'])->middleware(['allow:partner']);
Route::post('/cart/partner/clear', [PartnerCartController::class, 'clear'])->middleware(['allow:partner']);

Route::post('/cart/promoter', [PromoterCartController::class, 'get'])->middleware(['allow:partner']);
Route::post('/cart/promoter/add', [PromoterCartController::class, 'add'])->middleware(['allow:partner']);
Route::post('/cart/promoter/quantity', [PromoterCartController::class, 'quantity'])->middleware(['allow:partner']);
Route::post('/cart/promoter/remove', [PromoterCartController::class, 'remove'])->middleware(['allow:partner']);
Route::post('/cart/promoter/clear', [PromoterCartController::class, 'clear'])->middleware(['allow:partner']);

Route::post('/cart/terminal', [TerminalCartController::class, 'get'])->middleware(['allow:staff_terminal']);
Route::post('/cart/terminal/add', [TerminalCartController::class, 'add'])->middleware(['allow:staff_terminal']);
Route::post('/cart/terminal/quantity', [TerminalCartController::class, 'quantity'])->middleware(['allow:staff_terminal']);
Route::post('/cart/terminal/remove', [TerminalCartController::class, 'remove'])->middleware(['allow:staff_terminal']);
Route::post('/cart/terminal/clear', [TerminalCartController::class, 'clear'])->middleware(['allow:staff_terminal']);

Route::post('/cart/scheme/add', [SchemeCartController::class, 'add'])->middleware(['allow:staff_terminal,partner']);
Route::post('/cart/scheme/seats_reserve', [SchemeCartController::class, 'reserveSeat'])->middleware(['allow:staff_terminal,partner']);
