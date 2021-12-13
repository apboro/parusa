<?php

namespace App\Http\Controllers\API\Sails;

use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Sails\Excursion;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;

class ExcursionsListController extends ApiController
{
    protected array $defaultFilters = [
        'status_id' => ExcursionStatus::active,
    ];

    protected array $rememberFilters = [
        'status_id',
    ];

    protected string $rememberKey = 'excursions_list';

    /**
     * Get excursions list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = Excursion::query()
            ->with(['status'])
            ->orderBy('name');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey))) {
            if (!empty($filters['status_id'])) {
                $query->where('status_id', $filters['status_id']);
            }
        }

        // current page automatically resolved from request via `page` parameter
        $excursions = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var Collection $excursions */
        $excursions->transform(function (Excursion $excursions) {
            return [
                'active' => $excursions->hasStatus(ExcursionStatus::active),
                'id' => $excursions->id,
                'record' => [
                    'name' => $excursions->name,
                    'status' => $excursions->status->name,
                ],
            ];
        });

        return APIResponse::paginationList($excursions, [
            'name' => 'Название',
            'status' => 'Статус',
        ], [
            'filters' => $filters,
            'filters_original' => $this->defaultFilters,
        ])->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
