<?php

use App\Http\Controllers\API\Representatives\RepresentativeAccessController;
use App\Http\Controllers\API\Representatives\RepresentativeDeleteController;
use App\Http\Controllers\API\Representatives\RepresentativeEditController;
use App\Http\Controllers\API\Representatives\RepresentativePositionController;
use App\Http\Controllers\API\Representatives\RepresentativesListController;
use App\Http\Controllers\API\Representatives\RepresentativeStatusController;
use App\Http\Controllers\API\Representatives\RepresentativeViewController;
use Illuminate\Support\Facades\Route;

Route::post('/representatives', [RepresentativesListController::class, 'list'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/representatives/view', [RepresentativeViewController::class, 'view'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/representatives/attach', [RepresentativePositionController::class, 'attach'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/representatives/detach', [RepresentativePositionController::class, 'detach'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/representatives/status', [RepresentativeStatusController::class, 'setStatus'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/representatives/access/set', [RepresentativeAccessController::class, 'set'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/representatives/access/release', [RepresentativeAccessController::class, 'release'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/representatives/get', [RepresentativeEditController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/representatives/update', [RepresentativeEditController::class, 'update'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/representatives/delete', [RepresentativeDeleteController::class, 'delete'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
