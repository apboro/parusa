<?php

namespace App\Http\Controllers\Services\Sber;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class SberNotificationsController
{
    public function sberNotification(Request $request): Response
    {
        Log::channel('sber_payments')->info('Callback received: ' . json_encode($request->all()));

        return response('OK', 200);
    }
}
