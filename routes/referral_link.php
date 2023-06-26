<?php

use App\Http\Controllers\ReferralLink\ReferralLinkRedirectController;
use Illuminate\Support\Facades\Route;

Route::get('/referral/{id}', [ReferralLinkRedirectController::class, 'redirect'])->name('referral_link')->middleware(['checkout']);

