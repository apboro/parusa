<?php

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

use App\Http\Controllers\API\NotFoundController;
use Illuminate\Support\Facades\Route;

require base_path('routes/api/company.php');
require base_path('routes/api/settings.php');
require base_path('routes/api/partners.php');
require base_path('routes/api/dictionaries.php');
require base_path('routes/api/sails.php');
require base_path('routes/api/registries.php');

Route::any('{any}', [NotFoundController::class, 'notFound'])->where('any', '.*');
