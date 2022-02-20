<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\API\NotFoundController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth:sanctum'])->group(function () {
    require base_path('routes/api/staff.php');
    require base_path('routes/api/excursions.php');
    require base_path('routes/api/trips.php');
    require base_path('routes/api/piers.php');
    require base_path('routes/api/account.php');
    require base_path('routes/api/staff_company.php');
    require base_path('routes/api/staff_settings.php');
    require base_path('routes/api/staff_partners.php');
    require base_path('routes/api/staff_dictionaries.php');
    require base_path('routes/api/staff_sails.php');
    require base_path('routes/api/registries.php');
});

Route::prefix('partner')->middleware(['auth'])->group(function () {
    require base_path('routes/api/partner_partners.php');
});

Route::any('{any}', [NotFoundController::class, 'notFound'])->where('any', '.*');
