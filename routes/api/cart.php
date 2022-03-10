<?php

use App\Http\Controllers\API\Cart\PartnerCartController;
use App\Http\Controllers\API\Cart\TerminalCartController;
use Illuminate\Support\Facades\Route;

Route::post('/cart/partner', [PartnerCartController::class, 'get'])->middleware(['allow:partner']);
Route::post('/cart/partner/add', [PartnerCartController::class, 'add'])->middleware(['allow:partner']);
Route::post('/cart/partner/remove', [PartnerCartController::class, 'remove'])->middleware(['allow:partner']);

Route::post('/cart/terminal', [TerminalCartController::class, 'get'])->middleware(['allow:staff_terminal']);
Route::post('/cart/terminal/add', [TerminalCartController::class, 'add'])->middleware(['allow:staff_terminal']);
Route::post('/cart/terminal/remove', [TerminalCartController::class, 'remove'])->middleware(['allow:staff_terminal']);
