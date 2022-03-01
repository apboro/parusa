<?php

use App\Http\Controllers\API\Order\PartnerOrderController;
use Illuminate\Support\Facades\Route;

Route::post('/order/make', [PartnerOrderController::class, 'make']);
