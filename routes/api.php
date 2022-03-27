<?php

use App\Http\Controllers\API\NotFoundController;
use Illuminate\Support\Facades\Route;

Route::prefix('api')->middleware(['api', 'auth:sanctum'])->group(function () {

    require base_path('routes/api/dictionaries.php');

    require base_path('routes/api/trips.php'); // todo check
    require base_path('routes/api/piers.php');
    require base_path('routes/api/excursions.php');
    require base_path('routes/api/rates.php');

    require base_path('routes/api/registries.php'); // todo check

    require base_path('routes/api/cart.php'); // todo check
    require base_path('routes/api/order.php'); // todo check

    require base_path('routes/api/staff.php');
    require base_path('routes/api/partners.php'); // todo check
    require base_path('routes/api/representatives.php');
    require base_path('routes/api/terminals.php');

    require base_path('routes/api/account.php'); // todo check
    require base_path('routes/api/staff_settings.php'); // todo check

    Route::any('{any}', [NotFoundController::class, 'notFound'])->where('any', '.*');
});

