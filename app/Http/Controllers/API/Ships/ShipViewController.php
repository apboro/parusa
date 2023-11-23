<?php

namespace App\Http\Controllers\API\Ships;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Hit\Hit;
use App\Models\Ships\Seats\Seat;
use App\Models\Ships\Ship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipViewController extends ApiController
{
    public function view(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $id = $request->input('id');

        $ship = Ship::query()->where('id', $id)->first();

        if ($id === null || !$ship) {
            return APIResponse::notFound('Теплоход не найден');
        }
        $seats = Seat::query()->with('category')->where('ship_id', $request->id)->get();

        if ($seats->count() < $ship->capacity)
            for ($i = 1; $i <= $ship->capacity; $i++)
                Seat::query()->firstOrCreate([
                    'ship_id' => $ship->id,
                    'seat_number' => $i,
            ]);


        $categories = $ship->seats()->whereNotNull('seat_category_id')->groupBy('seat_category_id')->get();
            if ($categories->isNotEmpty()) {
                $categories->transform(fn($e) => ['name' => $e->category?->name, 'id' => $e->category?->id]);
                } else {
                $categories = [];
            }

        // fill data
        $values = [
            'active' => $ship->hasStatus(ShipStatus::active),
            'id' => $ship->id,
            'name' => $ship->name,
            'description' => $ship->description,
            'capacity' => $ship->capacity,
            'owner' => $ship->owner,
            'status' => $ship->status->name,
            'status_id' => $ship->status_id,
            'categories' => $categories,
            'seats' => $seats->transform(fn($seat) => ['seat_number' => $seat->seat_number, 'category' => $seat->category, 'status' => null]),
            'seat_tickets_grades' => $ship->seat_categories_ticket_grades()->with('grade')->get(),
            'ship_has_seats_scheme' => $ship->ship_has_seats_scheme,
        ];

        // send response
        return APIResponse::response($values);
    }
}
