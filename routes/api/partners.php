<?php

use App\Http\Controllers\API\Partners\PartnerDeleteController;
use App\Http\Controllers\API\Partners\PartnerDetailsController;
use App\Http\Controllers\API\Partners\PartnerEditController;
use App\Http\Controllers\API\Partners\PartnerInfoController;
use App\Http\Controllers\API\Partners\PartnerPropertiesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativeStatusController;
use App\Http\Controllers\API\Partners\PartnerSettingsController;
use App\Http\Controllers\API\Partners\PartnersListController;
use App\Http\Controllers\API\Partners\PartnerViewController;
use App\Http\Controllers\Storage\PartnerDocumentController;
use Illuminate\Support\Facades\Route;


Route::post('/partners', [PartnersListController::class, 'list'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/partners/view', [PartnerViewController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/partners/properties', [PartnerPropertiesController::class, 'properties'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/partners/representative/details', [PartnerRepresentativesController::class, 'details'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/partners/representative/attach', [PartnerRepresentativesController::class, 'attach'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/partners/representative/detach', [PartnerRepresentativesController::class, 'detach'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/partners/representative/status', [PartnerRepresentativeStatusController::class, 'setStatus'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/partners/get', [PartnerEditController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');
Route::post('/partners/update', [PartnerEditController::class, 'update'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::post('/partners/delete', [PartnerDeleteController::class, 'delete'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant');

Route::get('/partners/files/{file}', [PartnerDocumentController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant,partner');

Route::post('/partners/details', [PartnerDetailsController::class, 'get'])->middleware(['allow:partner']);

Route::post('/partners/partner/info', [PartnerInfoController::class, 'get'])->middleware(['allow:partner']);
Route::post('/partners/partner/settings', [PartnerSettingsController::class, 'settings'])->middleware(['allow:partner']);
Route::post('/partners/partner/settings/qr', [PartnerSettingsController::class, 'qr'])->middleware(['allow:partner']);
