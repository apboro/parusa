<?php

namespace App\Http\Controllers\API\Registries;

use App\Helpers\OrderPdf;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Tickets\Order;
use App\Models\User\Helpers\Currents;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderRenderController extends ApiController
{
    /**
     * Download ticket.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function download(Request $request): JsonResponse
    {
        $order = $this->order($request);

        if ($order === null) {
            return APIResponse::error('Заказ не найден');
        }

        $pdf = OrderPdf::a4($order);

        return APIResponse::response([
            'order' => base64_encode($pdf),
            'file_name' => "Заказ N$$order->id.pdf",
        ]);
    }

    /**
     * Download ticket print form.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function print(Request $request): JsonResponse
    {
        $order = $this->order($request);

        if ($order === null) {
            return APIResponse::error('Заказ не найден');
        }

        $pdf = OrderPdf::print($order);

        return APIResponse::response([
            'order' => base64_encode($pdf),
            'file_name' => "Заказ N$$order->id.pdf",
        ]);
    }

    /**
     * @param Request $request
     *
     * @return Order
     */
    protected function order(Request $request): ?Order
    {
        $current = Currents::get($request);

        return Order::query()
            ->where('id', $request->input('id'))
            ->when(!$current->isStaff(), function (Builder $query) use ($current) {
                $query->where('partner_id', $current->partnerId());
            })
            ->with([
                'status', 'type', 'tickets.grade', 'partner', 'position.user.profile', 'terminal',
                'tickets', 'tickets.status', 'tickets.trip', 'tickets.trip.excursion', 'tickets.trip.startPier',
            ])
            ->first();
    }
}
