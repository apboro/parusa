<?php

use App\Http\Controllers\API\News\NewsController;

Route::post('/news/get',[NewsController::class, 'get'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/news/update',[NewsController::class, 'update'])->middleware(['allow:staff_admin,staff_office_manager']);
