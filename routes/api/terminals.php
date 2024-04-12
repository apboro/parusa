<?php

use App\Http\Controllers\API\Promoters\WorkShiftController;
use App\Http\Controllers\API\Terminals\TerminalDeleteController;
use App\Http\Controllers\API\Terminals\TerminalEditController;
use App\Http\Controllers\API\Terminals\TerminalInfoController;
use App\Http\Controllers\API\Terminals\TerminalPropertiesController;
use App\Http\Controllers\API\Terminals\TerminalsListController;
use App\Http\Controllers\API\Terminals\TerminalStaffController;
use App\Http\Controllers\API\Terminals\TerminalViewController;
use Illuminate\Support\Facades\Route;

Route::post('/terminals', [TerminalsListController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);

Route::post('/terminals/view', [TerminalViewController::class, 'view'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/terminals/properties', [TerminalPropertiesController::class, 'properties'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/terminals/attach', [TerminalStaffController::class, 'attach'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/terminals/detach', [TerminalStaffController::class, 'detach'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);

Route::post('/terminals/get', [TerminalEditController::class, 'get'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);
Route::post('/terminals/update', [TerminalEditController::class, 'update'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);

Route::post('/terminals/delete', [TerminalDeleteController::class, 'delete'])->middleware(['allow:staff_admin,staff_office_manager,staff_accountant']);

// Info
Route::post('/terminals/terminal/info', [TerminalInfoController::class, 'info'])->middleware(['allow:staff_terminal']);

//promoters
Route::post('/terminals/promoters/open_work_shift', [WorkShiftController::class, 'open'])->middleware(['allow:staff_terminal']);
Route::post('/terminals/promoters/pay_work_shift', [WorkShiftController::class, 'pay'])->middleware(['allow:staff_terminal,staff_accountant,staff_admin']);
Route::post('/terminals/promoters/close_work_shift', [WorkShiftController::class, 'close'])->middleware(['allow:staff_terminal,staff_accountant,staff_admin']);
Route::post('/terminals/promoters/print_payout', [WorkShiftController::class, 'print'])->middleware(['allow:staff_terminal,staff_accountant,staff_admin']);
