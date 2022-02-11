<?php

use App\Http\Controllers\API\Trips\TripsListController;
use Illuminate\Support\Facades\Route;

Route::post('/trips', [TripsListController::class, 'list']);
