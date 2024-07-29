<?php

use App\Http\Controllers\Cities\CityController;
use App\Http\Controllers\ShowcaseForCities\ShowcaseForCitiesInitController;
use App\Http\Controllers\ShowcaseForCities\ShowcaseForCitiesTripsController;

Route::get('/cities/kazan', [CityController::class, 'kazan'])->name('cities.kazan.index');
Route::get('/city_showcase_data', [CityController::class, 'get'])->name('cities.get');
Route::post('/cities/trips', [ShowcaseForCitiesTripsController::class, 'trips']);
Route::post('/cities/init', [ShowcaseForCitiesInitController::class, 'init']);
