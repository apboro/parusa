<?php

use App\Http\Controllers\API\Excursions\ExcursionsListController;
use Illuminate\Support\Facades\Route;

Route::post('/excursions', [ExcursionsListController::class, 'list']);
