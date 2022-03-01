<?php

use App\Http\Controllers\API\Registries\OrdersRegistryItemController;
use App\Http\Controllers\API\Registries\OrdersRegistryController;
use App\Http\Controllers\API\Registries\ReservesRegistryController;
use App\Http\Controllers\API\Registries\TicketsRegistryItemController;
use App\Http\Controllers\API\Registries\TicketsRegistryController;
use Illuminate\Support\Facades\Route;

Route::post('/registries/orders', [OrdersRegistryController::class, 'list']);
Route::post('/registries/order', [OrdersRegistryItemController::class, 'view']);

Route::post('/registries/reserves', [ReservesRegistryController::class, 'list']);

Route::post('/registries/tickets', [TicketsRegistryController::class, 'list']);
Route::post('/registries/ticket', [TicketsRegistryItemController::class, 'view']);
