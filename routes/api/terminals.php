<?php

use App\Http\Controllers\API\Terminals\TerminalDeleteController;
use App\Http\Controllers\API\Terminals\TerminalEditController;
use App\Http\Controllers\API\Terminals\TerminalInfoController;
use App\Http\Controllers\API\Terminals\TerminalPropertiesController;
use App\Http\Controllers\API\Terminals\TerminalsListController;
use App\Http\Controllers\API\Terminals\TerminalStaffController;
use App\Http\Controllers\API\Terminals\TerminalViewController;
use Illuminate\Support\Facades\Route;

Route::post('/terminals', [TerminalsListController::class, 'list'])->middleware(['allow:staff_admin']);

Route::post('/terminals/view', [TerminalViewController::class, 'view'])->middleware(['allow:staff_admin']);
Route::post('/terminals/properties', [TerminalPropertiesController::class, 'properties'])->middleware(['allow:staff_admin']);
Route::post('/terminals/attach', [TerminalStaffController::class, 'attach'])->middleware(['allow:staff_admin']);
Route::post('/terminals/detach', [TerminalStaffController::class, 'detach'])->middleware(['allow:staff_admin']);

Route::post('/terminals/get', [TerminalEditController::class, 'get'])->middleware(['allow:staff_admin']);
Route::post('/terminals/update', [TerminalEditController::class, 'update'])->middleware(['allow:staff_admin']);

Route::post('/terminals/delete', [TerminalDeleteController::class, 'delete'])->middleware(['allow:staff_admin']);

// Info
Route::post('/terminals/terminal/info', [TerminalInfoController::class, 'info'])->middleware(['allow:staff_terminal']);
