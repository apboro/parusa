<?php

namespace App\Http\Controllers\API\Piers;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Hit\Hit;
use App\Models\Piers\Pier;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class PiersListController extends ApiController
{
    protected array $defaultFilters = [
        'status_id' => PiersStatus::active,
        'provider_id' => null,
    ];

    protected array $rememberFilters = [
        'status_id',
        'provider_id'
    ];

    protected string $rememberKey = CookieKeys::pier_list;

    /**
     * Get piers list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $query = Pier::query()
            ->with(['status'])
            ->orderBy('name');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }
        if (!empty($filters['provider_id'])) {
            $query->where('provider_id', $filters['provider_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $piers = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $piers */
        $piers->transform(function (Pier $pier) {
            return [
                'active' => $pier->hasStatus(PiersStatus::active),
                'id' => $pier->id,
                'name' => $pier->name,
                'provider_name' => $pier->provider->name,
                'provider_id' => $pier->provider->id,
                'status' => $pier->status->name,
            ];
        });

        return APIResponse::list(
            $piers, ['name' => 'Название', 'status' => 'Статус', 'source'=>'Владелец'],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
