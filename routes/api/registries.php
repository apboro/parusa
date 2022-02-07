<?php

use App\Http\Controllers\API\Registries\OrdersRegistry;
use App\Http\Controllers\API\Registries\ReservesRegistry;
use App\Http\Controllers\API\Registries\TicketsRegistry;
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

Route::post('/registries/orders', [OrdersRegistry::class, 'list']);

Route::post('/registries/orders/tickets', [OrdersRegistry::class, 'tickets']);
Route::post('/registries/reserves', [ReservesRegistry::class, 'list']);
Route::post('/registries/reserves/tickets', [ReservesRegistry::class, 'tickets']);
Route::post('/registries/tickets', [TicketsRegistry::class, 'list']);
