<?php

namespace App\Http\Controllers\API\Excursions;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\HitSource;
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class ExcursionsListController extends ApiController
{
    protected array $defaultFilters = [
        'status_id' => ExcursionStatus::active,
    ];

    protected array $rememberFilters = [
        'status_id',
    ];

    protected string $rememberKey = CookieKeys::excursions_list;

    /**
     * Get excursions list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $query = Excursion::query()
            ->with(['status'])
            ->orderBy('name');

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $excursions = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $excursions */
        $excursions->transform(function (Excursion $excursions) {
            return [
                'active' => $excursions->hasStatus(ExcursionStatus::active),
                'id' => $excursions->id,
                'name' => $excursions->name,
                'provider' => $excursions->provider()?->name,
                'status' => $excursions->status->name,
            ];
        });

        return APIResponse::list(
            $excursions,
            ['Название', 'Поставщик', 'Статус'],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
