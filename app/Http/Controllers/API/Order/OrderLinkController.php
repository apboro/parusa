<?php

namespace App\Http\Controllers\API\Order;

use App\Helpers\OrderPdf;
use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class OrderLinkController extends Controller
{
    public function getOrderPDFbyLink(string $hash)
    {
        $order = Order::query()
            ->whereIn('status_id', OrderStatus::order_printable_statuses)
            ->where('hash', $hash)->first();
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

    public function getPaymentLinkByHash(Request $request, string $hash)
    {
        $order = Order::query()
            ->whereIn('status_id', OrderStatus::sberpay_statuses)
            ->where('hash', $hash)
            ->first();

        if ($order && $order->additionalData?->provider_id !== Provider::scarlet_sails){
            $linkValidTime = 5;
        } else {
            $linkValidTime = 30;
        }

        if ($order && now() >= $order->created_at->addMinutes($linkValidTime)) {
            $orderSecret = json_encode([
                'id' => $order->id,
                'ts' => Carbon::now(),
                'ip' => $request->ip(),
                'ref' => $request->input('ref'),
            ], JSON_THROW_ON_ERROR);

            $secret = Crypt::encrypt($orderSecret);

            $link = config('showcase.showcase_payment_page') . '?order=' . $secret;

            return redirect($link);
        } else {

            return response()->view('order_not_found');
        }


    }
}
