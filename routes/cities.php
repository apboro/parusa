<?php

use App\Http\Controllers\Cities\CityController;

Route::get('/cities/kazan', [CityController::class, 'kazan'])->name('cities.kazan.index');
Route::get('/city_showcase_data', [CityController::class, 'get'])->name('cities.get');
