<?php

use App\Http\Controllers\API\Terminals\TerminalDeleteController;
use App\Http\Controllers\API\Terminals\TerminalEditController;
use App\Http\Controllers\API\Terminals\TerminalPropertiesController;
use App\Http\Controllers\API\Terminals\TerminalsListController;
use App\Http\Controllers\API\Terminals\TerminalStaffController;
use App\Http\Controllers\API\Terminals\TerminalViewController;
use Illuminate\Support\Facades\Route;

Route::post('/terminals', [TerminalsListController::class, 'list']);
Route::post('/terminals/view', [TerminalViewController::class, 'view']);
Route::post('/terminals/properties', [TerminalPropertiesController::class, 'properties']);

Route::post('/terminals/attach', [TerminalStaffController::class, 'attach']);
Route::post('/terminals/detach', [TerminalStaffController::class, 'detach']);

Route::post('/terminals/get', [TerminalEditController::class, 'get']);
Route::post('/terminals/update', [TerminalEditController::class, 'update']);

Route::post('/terminals/delete', [TerminalDeleteController::class, 'delete']);
