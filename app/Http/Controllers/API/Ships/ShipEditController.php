<?php

namespace App\Http\Controllers\API\Ships;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PartnerType;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Hit\Hit;
use App\Models\Partner\Partner;
use App\Models\Ships\Ship;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShipEditController extends ApiEditController
{
    protected array $rules = [
        'name' => 'required',
        'capacity' => 'required',
        'status_id' => 'required',
    ];

    protected array $titles = [
        'name' => 'Название',
        'description' => 'Описание',
        'capacity' => 'Вместимость',
        'owner' => 'Владелец',
        'ship_has_seats_scheme' => 'Схема рассадки',
        'status_id' => 'Статус',
        'partner_id' => 'Теплоход партнера'
    ];

    public function get(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);

        $ship = $this->firstOrNewShip($request);

        if ($ship === null) {
            return APIResponse::notFound('Теплоход не найден');
        }

        // send response
        return APIResponse::form(
            [
                'name' => $ship->name,
                'description' => $ship->description,
                'capacity' => (string)$ship->capacity,
                'ship_has_seats_scheme' => $ship->ship_has_seats_scheme,
                'status_id' => $ship->status_id,
                'partner_id' => $ship->partner?->id,
            ],
            $this->rules,
            $this->titles,
            [
                'name' => $ship->exists ? $ship->name : 'Добавление теплохода',
                'partners' => Partner::query()
                    ->where('type_id', PartnerType::ship_owner)
                    ->get(),
            ]
        );
    }

    public function update(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $data = $this->getData($request);

        if ($errors = $this->validate($data, $this->rules, $this->titles)) {
            return APIResponse::validationError($errors);
        }


        $ship = $this->firstOrNewShip($request);

        if ($ship === null) {
            return APIResponse::notFound('Теплоход не найден');
        }

        $ship->provider_id = Provider::scarlet_sails;
        $ship->name = $data['name'];
        $ship->capacity = $data['capacity'];
        $ship->owner = 'Алые Паруса';
        $ship->status_id = $data['status_id'];
        $ship->description = $data['description'];
        $ship->ship_has_seats_scheme = $data['ship_has_seats_scheme'];
        $ship->partner_id = $data['partner_id'];
        $ship->save();

        return APIResponse::success(
            $ship->wasRecentlyCreated ? 'Теплоход добавлен' : 'Данные теплохода обновлены',
            [
                'active' => $ship->hasStatus(ShipStatus::active),
                'id' => $ship->id,
                'name' => $ship->name,
                'description' => $ship->description,
                'capacity' => $ship->capacity,
                'owner' => $ship->owner,
                'status' => $ship->status->name,
                'status_id' => $ship->status_id,
                'ship_has_seats_scheme' => $ship->ship_has_seats_scheme,
                'partner_id' => $ship->partner?->id,
            ]
        );
    }

    protected function firstOrNewShip(Request $request, array $with = []): ?Ship
    {

        if (($id = $request->input('id')) === null) {
            return null;
        }

        $id = (int)$id;

        if ($id === 0) {
            return new Ship;
        }

        $ship = Ship::query()
            ->where('id', $id)
            ->with($with)
            ->first();

        return $ship ?? null;
    }
}
