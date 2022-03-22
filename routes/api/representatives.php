<?php

use App\Http\Controllers\API\Partners\Representatives\RepresentativeAccessController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeCardController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeDeleteController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeEditController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativePositionsController;
use App\Http\Controllers\API\Partners\Representatives\RepresentativeStatusController;
use App\Http\Controllers\API\Representatives\RepresentativesListController;
use Illuminate\Support\Facades\Route;

Route::post('/representatives', [RepresentativesListController::class, 'list'])->middleware('allow:staff_admin');


Route::post('/representatives/view', [RepresentativeCardController::class, 'get'])->middleware('allow:staff_admin');
Route::post('/representatives/get', [RepresentativeEditController::class, 'get'])->middleware('allow:staff_admin');
Route::post('/representatives/update', [RepresentativeEditController::class, 'update'])->middleware('allow:staff_admin');
Route::post('/representatives/status', [RepresentativeStatusController::class, 'setStatus'])->middleware('allow:staff_admin');
Route::post('/representatives/delete', [RepresentativeDeleteController::class, 'delete'])->middleware('allow:staff_admin');
Route::post('/representatives/attach', [RepresentativePositionsController::class, 'attach'])->middleware('allow:staff_admin');
Route::post('/representatives/detach', [RepresentativePositionsController::class, 'detach'])->middleware('allow:staff_admin');
Route::post('/representatives/access/set', [RepresentativeAccessController::class, 'set'])->middleware('allow:staff_admin');
Route::post('/representatives/access/release', [RepresentativeAccessController::class, 'release'])->middleware('allow:staff_admin');
