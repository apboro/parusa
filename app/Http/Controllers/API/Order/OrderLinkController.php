<?php

namespace App\Http\Controllers\API\Order;

use App\Helpers\OrderPdf;
use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class OrderLinkController extends Controller
{
    public function getOrderPDFbyLink(string $hash)
    {
        $order = Order::where('hash', $hash)->first();
        if ($order) {
            $pdf = OrderPdf::a4($order);
            return Response::make($pdf, 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => "inline; filename= Заказ N$order->id.pdf",
            ]);
        } else {
            return response()->view('order_not_found');
        }
    }
}
