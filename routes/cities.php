<?php

use App\Http\Controllers\Cities\CityController;

Route::get('/cities/kazan', [CityController::class, 'kazan'])->name('cities.kazan.index');
