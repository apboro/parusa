<?php

use App\Http\Controllers\API\Partners\PartnerCardController;
use App\Http\Controllers\API\Partners\PartnerDeleteController;
use App\Http\Controllers\API\Partners\PartnerEditController;
use App\Http\Controllers\API\Partners\PartnerRepresentativePositionsController;
use App\Http\Controllers\API\Partners\PartnerRepresentativeStatusController;
use App\Http\Controllers\API\Partners\PartnersListController;
use App\Http\Controllers\API\Partners\PartnerStatusController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeAccessController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeCardController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeDeleteController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeEditController;
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
Route::post('/partners/view', [PartnerCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/partners/get', [PartnerEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/partners/update', [PartnerEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/partners/status', [PartnerStatusController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/partners/reservable', [PartnerStatusController::class, 'setCanReserve'])->middleware('auth:sanctum');
Route::post('/partners/guides-tickets', [PartnerStatusController::class, 'setGuideTickets'])->middleware('auth:sanctum');
Route::post('/partners/delete', [PartnerDeleteController::class, 'delete'])->middleware('auth:sanctum');
Route::post('/partners/representative/status', [PartnerRepresentativeStatusController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/partners/representative/details', [PartnerRepresentativePositionsController::class, 'details'])->middleware('auth:sanctum');
Route::post('/partners/representative/attach', [PartnerRepresentativePositionsController::class, 'attach'])->middleware('auth:sanctum');
Route::post('/partners/representative/detach', [PartnerRepresentativePositionsController::class, 'detach'])->middleware('auth:sanctum');

Route::post('/representatives', [RepresentativeListController::class, 'list'])->middleware('auth:sanctum');
Route::post('/representatives/view', [RepresentativeCardController::class, 'get'])->middleware('auth:sanctum');
Route::post('/representatives/get', [RepresentativeEditController::class, 'get'])->middleware('auth:sanctum');
Route::post('/representatives/update', [RepresentativeEditController::class, 'update'])->middleware('auth:sanctum');
Route::post('/representatives/status', [RepresentativeStatusController::class, 'setStatus'])->middleware('auth:sanctum');
Route::post('/representatives/delete', [RepresentativeDeleteController::class, 'delete'])->middleware('auth:sanctum');
Route::post('/representatives/attach', [RepresentativePositionsController::class, 'attach'])->middleware('auth:sanctum');
Route::post('/representatives/detach', [RepresentativePositionsController::class, 'detach'])->middleware('auth:sanctum');
Route::post('/representatives/access/set', [RepresentativeAccessController::class, 'set'])->middleware('auth:sanctum');
Route::post('/representatives/access/release', [RepresentativeAccessController::class, 'release'])->middleware('auth:sanctum');
