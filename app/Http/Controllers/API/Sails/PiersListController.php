<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Sails\Pier;
use Illuminate\Support\Collection;
use Illuminate\Http\JsonResponse;

class PiersListController extends ApiController
{
    protected array $defaultFilters = [
        'status_id' => PiersStatus::active,
    ];

    protected array $rememberFilters = [
        'status_id',
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
        $query = Pier::query()
            ->with(['status'])
            ->orderBy('name');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['status_id'])) {
                $query->where('status_id', $filters['status_id']);
            }
        }

        // current page automatically resolved from request via `page` parameter
        $piers = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var Collection $piers */
        $piers->transform(function (Pier $pier) {
            return [
                'active' => $pier->hasStatus(PiersStatus::active),
                'id' => $pier->id,
                'record' => [
                    'name' => $pier->name,
                    'status' => $pier->status->name,
                ],
            ];
        });

        return APIResponse::paginationList($piers, [
            'name' => 'Название',
            'status' => 'Статус',
        ], [
            'filters' => $filters,
            'filters_original' => $this->defaultFilters,
        ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
