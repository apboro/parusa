<?php

namespace App\Http\Controllers\API\Ships\Seats;

use App\Http\APIResponse;
use App\Http\Controllers\Controller;
use App\Models\Ships\Seats\Seat;
use App\Models\Ships\Seats\ShipSeatCategoryTicketGrade;
use App\Models\Ships\Ship;
use Illuminate\Http\Request;

class SeatCategoriesEditController extends Controller
{
    public function get(Request $request)
    {

        return APIResponse::response('');
    }

    public function update(Request $request)
    {
        $ship = Ship::query()->with(['seats', 'seat_categories_ticket_grades'])->where('id', $request->shipId)->firstOrFail();

        foreach ($request->selectedSeats as $seat) {
            Seat::updateOrCreate(
                    ['ship_id' => $ship->id,'seat_number' => $seat],
                    ['seat_category_id' => $request->seatCategory]);
        }

        $ship->seat_categories_ticket_grades()->where('seat_category_id', $request->seatCategory)->delete();
        foreach ($request->seatGrades as $grade) {
            ShipSeatCategoryTicketGrade::create(
                    [
                        'ship_id'=>$ship->id,
                        'seat_category_id' => $request->seatCategory,
                        'ticket_grade_id' => $grade
                    ]);
            }

        return APIResponse::success('Категории сохранены');
    }
}
