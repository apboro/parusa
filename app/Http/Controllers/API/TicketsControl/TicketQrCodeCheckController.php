<?php

namespace App\Http\Controllers\API\TicketsControl;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class TicketQrCodeCheckController extends Controller
{
    public function getScanData(Request $request)
    {
        return response($request->rawValue);
    }
}
