<?php

namespace App\Http\Controllers\API\Terminals;

use App\Http\APIResponse;
use App\Http\Controllers\API\CookieKeys;
use App\Http\Controllers\ApiController;
use App\Http\Requests\APIListRequest;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\POS\Terminal;
use Illuminate\Http\JsonResponse;
use Illuminate\Pagination\LengthAwarePaginator;

class TerminalsListController extends ApiController
{
    protected array $defaultFilters = [
        'status_id' => TerminalStatus::enabled,
    ];

    protected array $rememberFilters = [
        'status_id',
    ];

    protected string $rememberKey = CookieKeys::terminals_list;

    /**
     * Get terminals list.
     *
     * @param ApiListRequest $request
     *
     * @return  JsonResponse
     */
    public function list(ApiListRequest $request): JsonResponse
    {
        $query = Terminal::query()
            ->with(['status', 'pier', 'pier.info']);

        // apply filters
        if (!empty($filters = $request->filters($this->defaultFilters, $this->rememberFilters, $this->rememberKey)) && !empty($filters['status_id'])) {
            $query->where('status_id', $filters['status_id']);
        }

        // current page automatically resolved from request via `page` parameter
        $terminals = $query->paginate($request->perPage(10, $this->rememberKey));

        /** @var LengthAwarePaginator $terminals */
        $terminals->transform(function (Terminal $terminal) {
            return [
                'active' => $terminal->hasStatus(TerminalStatus::enabled),
                'id' => $terminal->id,
                'status_id' => $terminal->status_id,
                'status' => $terminal->status->name,
                'pier' => $terminal->pier->name,
                'place' => $terminal->pier->info->address,
            ];
        });

        return APIResponse::list(
            $terminals,
            [],
            $filters,
            $this->defaultFilters,
            []
        )->withCookie(cookie($this->rememberKey, $request->getToRemember()));
    }
}
