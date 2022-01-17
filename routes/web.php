<?php

use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Front\FrontendController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::get('/login', [AuthController::class, 'form'])->middleware('guest')->name('login');
Route::post('/login', [AuthController::class, 'login'])->middleware('guest');
Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth')->name('logout');
Route::get('/login/token', [AuthController::class, 'token'])->name('login.token.refresh');

Route::post('/login/select', [FrontendController::class, 'select'])->middleware('auth');
Route::name('frontend')
    ->any('/{query?}', [FrontendController::class, 'frontend'])
    ->where('query', '[\/\w\.-]*')
    ->middleware(['web', 'auth']);
