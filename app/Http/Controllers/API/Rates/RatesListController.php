<?php

namespace App\Http\Controllers\API\Rates;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\HitSource;
use App\Models\Excursions\Excursion;
use App\Models\Hit\Hit;
use App\Models\Tickets\TicketsRatesList;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RatesListController extends ApiEditController
{
    use RateToArray;

    /**
     * Get rates for excursion.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function list(Request $request): JsonResponse
    {
        Hit::register(HitSource::admin);
        $current = Currents::get($request);
        $now = Carbon::now();

        $partnerId = $current->isStaff() ? $request->input('partner_id') : $current->partnerId();
        $excursionId = $request->input('excursion_id');
        $isArchiveRequested = $current->isStaff() && $request->input('archive');
        $hideDisabledExcursions = ($excursionId === null);

        $query = TicketsRatesList::query()
            ->with(['rates', 'excursion'])
            // attach partner rates if requested
            ->when($partnerId !== null, function (Builder $query) use ($partnerId) {
                $query->with('rates.partnerRates', static function (HasMany $query) use ($partnerId) {
                    $query->where('partner_id', $partnerId);
                });
            })
            // filter by excursion if requested
            ->when($excursionId !== null, function (Builder $query) use ($excursionId) {
                $query->where('excursion_id', $excursionId);
            })
            // list only current rates for non-staff users
            ->when(!$current->isStaff(), function (Builder $query) use ($now) {
                $query->whereDate('start_at', '<=', $now)->whereDate('end_at', '>=', $now);
            })
            // select actual and coming or archive rates for staff users
            ->when($current->isStaff() && !$isArchiveRequested, function (Builder $query) use ($now) {
                $query->whereDate('end_at', '>=', $now);
            })
            ->leftJoin('excursions', 'excursions.id', '=', 'tickets_rates_list.excursion_id')
            ->when($hideDisabledExcursions, function (Builder $query) {
                $query->where('excursions.status_id', ExcursionStatus::active);
            })
            ->select('tickets_rates_list.*')
            ->orderBy('excursions.name')
            ->orderBy('tickets_rates_list.start_at');

        $list = $query->get();

        /** @var Collection $list */
        $list = $list->map(function (TicketsRatesList $ratesList) use($current) {
            return $this->rateToArray($ratesList, true, $current->isStaff());
        });

        $excursions = $excursionId !== null ? null : Excursion::query()
            ->select(['id', 'name'])
            ->when($hideDisabledExcursions, function (Builder $query) {
                $query->where('status_id', ExcursionStatus::active);
            })
            ->when($current->isRepresentative(), function (Builder $query) use ($current) {
                $query->withCount(['partnerShowcaseHide' => function (Builder $query) use($current){
                    $query->where('partner_id', $current->partnerId());
                }]);
            })
            ->orderBy('name')
            ->get();

        return APIResponse::response($list, [
            'today' => Carbon::now()->format('Y-m-d'),
            'excursions' => $excursions,
        ]);
    }
}
