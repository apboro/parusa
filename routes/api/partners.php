<?php

use App\Http\Controllers\API\Partners\PartnersListController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeAccessController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeCardController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeDeleteController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeListController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativePositionsController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeStatusController;
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

Route::post('/partners', [PartnersListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/partners/view', [PartnersListController::class, 'get'])->middleware('auth:sanctum');
Route::post('/partners/get', [PartnersListController::class, 'get'])->middleware('auth:sanctum');
Route::post('/partners/update', [PartnersListController::class, 'update'])->middleware('auth:sanctum');
Route::post('/partners/status', [PartnersListController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/partners/delete', [PartnersListController::class, 'delete'])->middleware('auth:sanctum');

Route::post('/representatives', [RepresentativeListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/representatives/view', [RepresentativeCardController::class, 'get'])->middleware('auth:sanctum');

//Route::post('/representatives/get', [StaffEditController::class, 'get'])->middleware('auth:sanctum');
//Route::post('/representatives/update', [StaffEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/representatives/status', [RepresentativeStatusController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/representatives/delete', [RepresentativeDeleteController::class, 'delete'])->middleware('auth:sanctum');
Route::post('/representatives/attach', [RepresentativePositionsController::class, 'attach'])->middleware('auth:sanctum');
Route::post('/representatives/detach', [RepresentativePositionsController::class, 'detach'])->middleware('auth:sanctum');
Route::post('/representatives/access/set', [RepresentativeAccessController::class, 'set'])->middleware('auth:sanctum');
Route::post('/representatives/access/release', [RepresentativeAccessController::class, 'release'])->middleware('auth:sanctum');
