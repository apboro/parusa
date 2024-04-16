<?php

use App\Http\Controllers\API\News\NewsUploadController;
use App\Http\Controllers\API\Order\OrderLinkController;

Route::get('/external/order/{hash}', [OrderLinkController::class, 'getOrderPDFbyLink'])->name('external.order.print');
Route::get('/ext/order/payment/{hash}', [OrderLinkController::class, 'getPaymentLinkByHash'])->name('external.order.pay');
Route::post('/news/images', [NewsUploadController::class, 'images']);
