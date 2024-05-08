<?php

use App\Http\Controllers\API\Promoters\PromoterDeleteController;
use App\Http\Controllers\API\Promoters\PromoterEditController;
use App\Http\Controllers\API\Promoters\PromoterInfoController;
use App\Http\Controllers\API\Promoters\PromoterInventoryController;
use App\Http\Controllers\API\Promoters\PromoterPayOutController;
use App\Http\Controllers\API\Promoters\PromotersListController;
use App\Http\Controllers\API\Promoters\PromoterViewController;
use App\Http\Controllers\API\Promoters\WorkShiftController;
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
Route::post('/promoters/change_commissions', [WorkShiftController::class, 'changeCommissions'])->middleware(['allow:staff_accountant,staff_admin,staff_promoter_manager']);

Route::post('/promoters/open_work_shift', [WorkShiftController::class, 'openSelfPay'])->middleware(['allow:partner']);

Route::post('/promoters/pay_outs', [PromoterPayOutController::class, 'list'])->middleware(['allow:staff_accountant,staff_admin,staff_promoter_manager,staff_terminal']);
