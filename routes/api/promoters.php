<?php

use App\Http\Controllers\API\Partners\PartnerDeleteController;
use App\Http\Controllers\API\Partners\PartnerDetailsController;
use App\Http\Controllers\API\Partners\PartnerEditController;
use App\Http\Controllers\API\Partners\PartnerInfoController;
use App\Http\Controllers\API\Partners\PartnerPropertiesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativesController;
use App\Http\Controllers\API\Partners\PartnerRepresentativeStatusController;
use App\Http\Controllers\API\Partners\PartnerViewController;
use App\Http\Controllers\API\Promoters\PromoterDeleteController;
use App\Http\Controllers\API\Promoters\PromoterEditController;
use App\Http\Controllers\API\Promoters\PromoterInfoController;
use App\Http\Controllers\API\Promoters\PromoterInventoryController;
use App\Http\Controllers\API\Promoters\PromotersListController;
use App\Http\Controllers\API\Promoters\PromoterViewController;
use Illuminate\Support\Facades\Route;

Route::post('/promoters', [PromotersListController::class, 'list'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant,staff_terminal,staff_promoter_manager');

Route::post('/promoters/view', [PromoterViewController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant,staff_terminal,staff_promoter_manager');
Route::get('/promoters/{promoter}/inventory', [PromoterInventoryController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_terminal,staff_promoter_manager');
Route::post('/promoters/inventory',  [PromoterInventoryController::class, 'store'])->middleware('allow:staff_admin,staff_office_manager,staff_terminal,staff_promoter_manager');
Route::post('/promoters/inventory/get',  [PromoterInventoryController::class, 'getForPromotersPage'])->middleware('allow:partner');

Route::post('/promoters/get', [PromoterEditController::class, 'get'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant,staff_promoter_manager');
Route::post('/promoters/update', [PromoterEditController::class, 'update'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant,staff_promoter_manager');

Route::post('/promoters/delete', [PromoterDeleteController::class, 'delete'])->middleware('allow:staff_admin,staff_office_manager,staff_accountant,staff_promoter_manager');

Route::post('/promoters/promoter/info', [PromoterInfoController::class, 'get'])->middleware(['allow:partner']);
