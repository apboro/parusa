<?php

use App\Http\Controllers\API\Partners\PartnerDeleteController;
use App\Http\Controllers\API\Partners\PartnerEditController;
use App\Http\Controllers\API\Partners\PartnerPropertiesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativeStatusController;
use App\Http\Controllers\API\Partners\PartnersListController;
use App\Http\Controllers\API\Partners\PartnerViewController;
use App\Http\Controllers\PartnerAPI\PartnerInfoController;
use App\Http\Controllers\PartnerAPI\PartnerSelfController;
use App\Http\Controllers\Storage\PartnerDocumentController;
use Illuminate\Support\Facades\Route;

Route::post('/partners', [PartnersListController::class, 'list'])->middleware('allow:staff_admin');

Route::post('/partners/view', [PartnerViewController::class, 'get'])->middleware('allow:staff_admin');
Route::post('/partners/properties', [PartnerPropertiesController::class, 'properties'])->middleware('allow:staff_admin');

Route::post('/partners/get', [PartnerEditController::class, 'get'])->middleware('allow:staff_admin');
Route::post('/partners/update', [PartnerEditController::class, 'update'])->middleware('allow:staff_admin');

Route::post('/partners/representative/details', [PartnerRepresentativesController::class, 'details'])->middleware('allow:staff_admin');
Route::post('/partners/representative/attach', [PartnerRepresentativesController::class, 'attach'])->middleware('allow:staff_admin');
Route::post('/partners/representative/detach', [PartnerRepresentativesController::class, 'detach'])->middleware('allow:staff_admin');
Route::post('/partners/representative/status', [PartnerRepresentativeStatusController::class, 'setStatus'])->middleware('allow:staff_admin');

Route::post('/partners/delete', [PartnerDeleteController::class, 'delete'])->middleware('allow:staff_admin');

Route::get('/partners/files/{file}', [PartnerDocumentController::class, 'get'])->middleware('allow:staff_admin,partner');







Route::post('/partners/partner/view', [PartnerSelfController::class, 'get'])->middleware(['allow:partner']);
Route::post('/partners/partner/info', [PartnerInfoController::class, 'get'])->middleware(['allow:partner']);
