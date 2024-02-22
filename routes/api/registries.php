<?php

use App\Http\Controllers\API\Registries\FillingRegistryController;
use App\Http\Controllers\API\Registries\OrderRenderController;
use App\Http\Controllers\API\Registries\OrderSendController;
use App\Http\Controllers\API\Registries\OrdersRegistryBuyerController;
use App\Http\Controllers\API\Registries\OrdersRegistryItemController;
use App\Http\Controllers\API\Registries\OrdersRegistryController;
use App\Http\Controllers\API\Registries\PromotersRegistryController;
use App\Http\Controllers\API\Registries\PromotersShiftsRegistryController;
use App\Http\Controllers\API\Registries\ReservesRegistryController;
use App\Http\Controllers\API\Registries\TicketRenderController;
use App\Http\Controllers\API\Registries\TicketSendController;
use App\Http\Controllers\API\Registries\TicketsRegistryItemController;
use App\Http\Controllers\API\Registries\TicketsRegistryController;
use App\Http\Controllers\API\Registries\TransactionsRegistryController;
use Illuminate\Support\Facades\Route;

Route::post('/registries/orders', [OrdersRegistryController::class, 'list'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/order', [OrdersRegistryItemController::class, 'view'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/order/buyer', [OrdersRegistryBuyerController::class, 'buyer'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,partner']);

Route::post('/registries/filling', [FillingRegistryController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,partner']);
Route::post('/registries/filling/export', [FillingRegistryController::class, 'export'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,partner']);


Route::post('/registries/reserves', [ReservesRegistryController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,staff_terminal,partner']);

Route::post('/registries/tickets', [TicketsRegistryController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/tickets/export', [TicketsRegistryController::class, 'export'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/ticket', [TicketsRegistryItemController::class, 'view'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant,staff_terminal,partner']);

Route::post('/registries/transactions', [TransactionsRegistryController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,staff_terminal']);
Route::post('/registries/transactions/fiscal', [TransactionsRegistryController::class, 'fiscal'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,staff_terminal']);
Route::post('/registries/transactions/export', [TransactionsRegistryController::class, 'export'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant,staff_terminal']);

Route::post('/registries/ticket/download', [TicketRenderController::class, 'download'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/ticket/print', [TicketRenderController::class, 'print'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/ticket/send', [TicketSendController::class, 'send'])->middleware(['allow:staff_admin,staff_office_manager,staff_piers_manager,staff_accountant,staff_terminal,partner']);

Route::post('/registries/order/download', [OrderRenderController::class, 'download'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/order/print', [OrderRenderController::class, 'print'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_accountant,staff_terminal,partner']);
Route::post('/registries/order/send', [OrderSendController::class, 'send'])->middleware(['allow:staff_admin,staff_piers_manager,staff_office_manager,staff_accountant,staff_terminal,partner']);

Route::post('/registries/promoters/shifts', [PromotersShiftsRegistryController::class, 'list'])->middleware(['allow:staff_admin,staff_accountant,staff_promoter_manager']);
Route::post('/registries/promoters', [PromotersRegistryController::class, 'list'])->middleware(['allow:staff_admin,staff_accountant,staff_promoter_manager']);
