<?php

namespace App\Http\Controllers\Services\LifePay;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class CloudPrintNotificationsController
{
    public function cloudPrintNotification(Request $request): Response
    {
        if(env('CLOUD_PRINT_LOG')) {
            Log::channel('cloud_print')->info('Callback received: ' . json_encode($request->all()));
        }

        return response('OK', 200);
    }
}
