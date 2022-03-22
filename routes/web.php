<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Front\FrontendController;
use Illuminate\Support\Facades\Route;

Route::middleware('web')->group(function () {
    Route::get('/login', [AuthController::class, 'form'])->middleware('guest')->name('login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
    Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
    Route::get('/login/token', [AuthController::class, 'token'])->name('login.token.refresh');
    Route::post('/login/select', [FrontendController::class, 'select'])->middleware('auth');
    Route::post('/login/change', [FrontendController::class, 'change'])->middleware('auth');
});

Route::name('frontend')
    ->any('/{query?}', [FrontendController::class, 'index'])
    ->where('query', '[\/\w\.-]*')
    ->middleware(['web', 'auth']);
