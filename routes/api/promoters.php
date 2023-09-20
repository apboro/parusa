<?php

use App\Http\Controllers\API\Partners\PartnerDeleteController;
use App\Http\Controllers\API\Partners\PartnerDetailsController;
use App\Http\Controllers\API\Partners\PartnerEditController;
use App\Http\Controllers\API\Partners\PartnerInfoController;
use App\Http\Controllers\API\Partners\PartnerPropertiesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativeStatusController;
use App\Http\Controllers\API\Partners\PartnerViewController;
use App\Http\Controllers\API\Promoters\PromoterEditController;
use App\Http\Controllers\API\Promoters\PromotersListController;
use App\Http\Controllers\API\Promoters\PromoterViewController;
use Illuminate\Support\Facades\Route;

Route::post('/promoters', [PromotersListController::class, 'list'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/promoters/view', [PromoterViewController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/promoters/representative/details', [PartnerRepresentativesController::class, 'details'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/promoters/representative/attach', [PartnerRepresentativesController::class, 'attach'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/promoters/representative/detach', [PartnerRepresentativesController::class, 'detach'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/promoters/representative/status', [PartnerRepresentativeStatusController::class, 'setStatus'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/promoters/get', [PromoterEditController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/promoters/update', [PromoterEditController::class, 'update'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/promoters/delete', [PartnerDeleteController::class, 'delete'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/promoters/details', [PartnerDetailsController::class, 'get'])->middleware(['allow:partner']);

Route::post('/promoters/partner/info', [PartnerInfoController::class, 'get'])->middleware(['allow:partner']);
