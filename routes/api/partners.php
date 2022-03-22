<?php

use App\Http\Controllers\API\Partners\PartnerCardController;
use App\Http\Controllers\API\Partners\PartnerDeleteController;
use App\Http\Controllers\API\Partners\PartnerEditController;
use App\Http\Controllers\API\Partners\PartnerRepresentativePositionsController;
use App\Http\Controllers\API\Partners\PartnerRepresentativeStatusController;
use App\Http\Controllers\API\Partners\PartnersListController;
use App\Http\Controllers\API\Partners\PartnerStatusController;
use App\Http\Controllers\API\Tickets\PartnerRatesController;
use App\Http\Controllers\PartnerAPI\PartnerInfoController;
use App\Http\Controllers\PartnerAPI\PartnerSelfController;
use App\Http\Controllers\Storage\PartnerDocumentController;
use Illuminate\Support\Facades\Route;

Route::post('/partners', [PartnersListController::class, 'list'])->middleware('allow:staff_admin');


Route::post('/partners/view', [PartnerCardController::class, 'get'])->middleware('allow:staff_admin');
Route::post('/partners/get', [PartnerEditController::class, 'get'])->middleware('allow:staff_admin');
Route::post('/partners/update', [PartnerEditController::class, 'update'])->middleware('allow:staff_admin');
Route::post('/partners/status', [PartnerStatusController::class, 'setStatus'])->middleware('allow:staff_admin');
Route::post('/partners/reservable', [PartnerStatusController::class, 'setCanReserve'])->middleware('allow:staff_admin');
Route::post('/partners/guides-tickets', [PartnerStatusController::class, 'setGuideTickets'])->middleware('allow:staff_admin');
Route::post('/partners/delete', [PartnerDeleteController::class, 'delete'])->middleware('allow:staff_admin');
Route::post('/partners/representative/status', [PartnerRepresentativeStatusController::class, 'setStatus'])->middleware('allow:staff_admin');
Route::post('/partners/representative/details', [PartnerRepresentativePositionsController::class, 'details'])->middleware('allow:staff_admin');
Route::post('/partners/representative/attach', [PartnerRepresentativePositionsController::class, 'attach'])->middleware('allow:staff_admin');
Route::post('/partners/representative/detach', [PartnerRepresentativePositionsController::class, 'detach'])->middleware('allow:staff_admin');
Route::post('/partners/rates', [PartnerRatesController::class, 'get'])->middleware('allow:staff_admin');
Route::post('/partners/rates/update', [PartnerRatesController::class, 'update'])->middleware('allow:staff_admin');



Route::get('/partners/files/{file}', [PartnerDocumentController::class, 'get'])->middleware('auth:sanctum');

Route::post('/partners/partner/view', [PartnerSelfController::class, 'get'])->middleware(['allow:partner']);
Route::post('/partners/partner/info', [PartnerInfoController::class, 'get'])->middleware(['allow:partner']);
