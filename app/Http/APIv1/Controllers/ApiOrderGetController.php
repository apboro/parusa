<?php

namespace App\Http\APIv1\Controllers;

use App\Http\APIv1\Requests\ApiOrderGetRequest;
use App\Http\APIv1\Resources\ApiOrderResource;
use App\Http\Controllers\Controller;
use App\Models\Order\Order;

class ApiOrderGetController extends Controller
{
    public function __invoke(ApiOrderGetRequest $request)
    {
        $partner = auth()->user();

        $orders = Order::query()
            ->with(['status', 'tickets'])
            ->where('partner_id', $partner->id)
            ->when($request->order_id, fn ($query) => $query->where('id', $request->order_id))
            ->when($request->date_from, fn ($query) => $query->whereDate('created_at', '>=', $request->date_from))
            ->when($request->date_to, fn ($query) => $query->whereDate('created_at', '<=', $request->date_to))
            ->when(!$request->date_from, fn ($query) => $query->whereDate('created_at', '>=', now()->startOfMonth()))
            ->get();

        return response()->json(ApiOrderResource::collection($orders));
    }
}
