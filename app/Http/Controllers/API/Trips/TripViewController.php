<?php

namespace App\Http\Controllers\API\Trips;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Sails\Trip;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TripViewController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        $id = $request->input('id');

        if ($id === null ||
            null === ($trip = Trip::query()->with(['startPier', 'endPier', 'ship', 'excursion', 'status', 'saleStatus', 'discountStatus'])->where('id', $id)->first())) {
            return APIResponse::notFound('Рейс не найден');
        }

        /** @var Trip $trip */

        $values = [
            'id' => $trip->id,
            'name' => $trip->name,
            'start_at' => $trip->start_at->format('d.m.Y H:i'),
            'end_at' => $trip->end_at->format('d.m.Y H:i'),
            'duration' => $trip->start_at->diffInMinutes($trip->end_at),
            'start_pier' => $trip->startPier->name,
            'end_pier' => $trip->endPier->name,
            'ship' => $trip->ship->name,
            'excursion' => $trip->excursion->name,
            'status' => $trip->status->name,
            'status_id' => $trip->status_id,
            'sale_status' => $trip->saleStatus->name,
            'sale_status_id' => $trip->sale_status_id,
            'tickets_total' => $trip->tickets_total,
            'tickets_sold' => $trip->tickets()->whereIn('status_id', TicketStatus::ticket_sold_statuses)->count(),
            'tickets_reserved' => $trip->tickets()->whereIn('status_id', TicketStatus::ticket_reserved_statuses)->count(),
            'discount_status' => $trip->discountStatus->name,
            'discount_status_id' => $trip->discount_status_id,
            'cancellation_time' => $trip->cancellation_time,
        ];

        return APIResponse::response($values);
    }
}
