<?php

use App\Http\Controllers\API\Order\OrderLinkController;

Route::get('/external/order/{hash}', [OrderLinkController::class, 'getOrderPDFbyLink'])->name('external.order');
