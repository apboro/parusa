<?php

use App\Http\Controllers\API\News\NewsController;
use App\Http\Controllers\API\News\NewsSendController;

Route::post('/news',[NewsController::class, 'list'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/news/get',[NewsController::class, 'get'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/news/view',[NewsController::class, 'view'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/news/update',[NewsController::class, 'update'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/news/delete',[NewsController::class, 'delete'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/news/send',[NewsSendController::class, 'send'])->middleware(['allow:staff_admin,staff_office_manager']);
Route::post('/news/copy',[NewsController::class, 'copy'])->middleware(['allow:staff_admin,staff_office_manager']);

