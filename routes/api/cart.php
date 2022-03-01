<?php

use App\Http\Controllers\API\Cart\TicketsAddController;
use Illuminate\Support\Facades\Route;

Route::post('/cart/add', [TicketsAddController::class, 'add']);
