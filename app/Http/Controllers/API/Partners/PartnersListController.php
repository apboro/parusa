<?php

namespace App\Http\Controllers\API\Partners;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Partner\Partner;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class PartnersListController extends ApiController
{
    public function partnersList(APIListRequest $request): JsonResponse
    {
        $query = Partner::query()->with(['type', 'status']);

        // apply filters
        // apply search

        // current page automatically resolved from request via `page` parameter
        $partners = $query->paginate($request->perPage());

        /** @var Collection $partners */
        $partners->transform(function (Partner $partner) {
            return [
                // TODO fix to mysql $partner->hasStatus(PartnerStatus::active)
                'active' => (int)$partner->status_id === PartnerStatus::active,
                'record' => [
                    'name' => $partner->name,
                    'representatives' => [],
                    'type' => $partner->type->name,
                    'balance' => null,
                    'limit' => null,
                ],
            ];
        });

        // available types ids
        $types = Partner::query()->select('type_id')->distinct()->pluck('type_id')->toArray();

        return APIResponse::paginationList($partners, [
            'name' => 'Название партнера',
            'representatives' => 'Представители партнера',
            'type' => 'Тип партнера',
            'balance' => 'Лицевой счет',
            'limit' => 'Лимит',
        ], [
            'available_types' => $types,
        ]);
    }

}
