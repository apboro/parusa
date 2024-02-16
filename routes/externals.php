<?php

use App\Http\Controllers\API\News\NewsUploadController;
use App\Http\Controllers\API\Order\OrderLinkController;

Route::get('/external/order/{hash}', [OrderLinkController::class, 'getOrderPDFbyLink'])->name('external.order');
Route::get('/ext/order/payment/{hash}', [OrderLinkController::class, 'getPaymentLinkByHash'])->name('external.order');
Route::post('/news/images', [NewsUploadController::class, 'images']);
