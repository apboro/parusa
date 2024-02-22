<?php

namespace App\Http\Controllers\API\Ships;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Hit\Hit;
use App\Models\Ships\Ship;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ShipListController extends ApiController
{


    /**
     * Get staff list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $query = Ship::select();

        $filters = $request->filters();
        // apply search
        if (!empty($search = $request->search())) {
            foreach ($search as $term) {
                $query->where('name', 'LIKE', "%$term%");
            }
        }
        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $ships = $query->paginate($request->perPage());

        /** @var LengthAwarePaginator $users */
        $ships->transform(function (Ship $ship) {

            return [
                'active' => $ship->hasStatus(ShipStatus::active),
                'id' => $ship->id,
                'name' => $ship->name,
                'description' => $ship->description,
                'capacity' => $ship->capacity,
                'owner' => $ship->owner,
                'partner' => $ship->partner?->name
            ];
        });

        return APIResponse::list(
            $ships,
            [
                'name' => 'Название',
                'owner' =>  'Владелец',
                'capacity' => 'Вместимость',
            ],
            $filters,
            [],
            []
        );
    }
}
