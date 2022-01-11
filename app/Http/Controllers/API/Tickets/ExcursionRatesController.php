<?php

namespace App\Http\Controllers\API\Tickets;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Sails\Excursion;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class ExcursionRatesController extends ApiEditController
{
    /**
     * Get rates for excursion.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function get(Request $request): JsonResponse
    {
        if (null === ($id = $request->input('id')) || null === ($excursion = Excursion::query()->with(['ratesLists', 'ratesLists.rates'])->where('id', $id)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        /** @var Excursion $excursion */
        $rates = $excursion->ratesLists->map(function (TicketsRatesList $ratesList) {
            return $this->rateListToArray($ratesList);
        });

        return APIResponse::response($rates, [
            'today' => Carbon::now()->format('d.m.Y'),
        ]);
    }

    /**
     * Update rate.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function update(Request $request): JsonResponse
    {
        if (
            null === ($excursionId = $request->input('excursionId')) ||
            null === (Excursion::query()->with('ratesLists')->where('id', $excursionId)->first())
        ) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        if (null === ($ratesList = $this->firstOrNew(TicketsRatesList::class, $request, ['rates']))) {
            return APIResponse::notFound('Тариф не найден');
        }
        /** @var TicketsRatesList $ratesList */

        $flat = $request->input('data');
        $data = Arr::undot($flat);

        // make dynamic validation rules and check
        $count = count($data['rates'] ?? []);
        $rules = [
            'start_at' => 'required|date|after_or_equal:' . Carbon::now()->format('Y-m-d'),
            'end_at' => 'required|date|after:start_at',
        ];
        $titles = [
            'start_at' => 'Начало действия тарифа',
            'end_at' => 'Окончание действия тарифа',
        ];
        for ($i = 0; $i < $count; $i++) {
            $rules["rates.$i.base_price"] = 'required|integer|min:0|bail';
            $rules["rates.$i.min_price"] = 'required|integer|min:0|bail';
            $rules["rates.$i.max_price"] = "required|integer|gte:rates.$i.base_price|min:0|bail";
            $rules["rates.$i.commission_type"] = 'required';
            $rules["rates.$i.commission_value"] = 'required|integer|min:0|bail';
            $titles["rates.$i.base_price"] = 'Базовая цена';
            $titles["rates.$i.min_price"] = 'Минимальная';
            $titles["rates.$i.max_price"] = 'Максимальная';
            $titles["rates.$i.commission_type"] = 'Тип';
            $titles["rates.$i.commission_value"] = 'Комиссия';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::formError($flat, $rules, $titles, $errors);
        }

        // Check intervals collapsing
        $startAt = Carbon::parse($data['start_at']);
        $endAt = Carbon::parse($data['end_at']);
        $nextStart = null;
        $lists = TicketsRatesList::query()
            ->select(['start_at', 'end_at', 'id'])
            ->where('excursion_id', $excursionId)
            ->where('id', '<>', $ratesList->id)
            ->get();
        foreach ($lists as $item) {
            /** @var TicketsRatesList $item */
            if ($startAt <= $item->start_at && ($nextStart === null || $item->start_at < $nextStart)) {
                $nextStart = $item->start_at;
            }
            if ($item->start_at <= $startAt && $startAt <= $item->end_at) {
                return APIResponse::formError($flat, $rules, $titles, [
                    'start_at' => ['Начало действия тарифа пересекается другим тарифом'],
                ]);
            }
        }
        if ($nextStart !== null && $endAt >= $nextStart) {
            return APIResponse::formError($flat, $rules, $titles, [
                'start_at' => ['Действие тарифа пересекается другим тарифом'],
                'end_at' => ['Действие тарифа пересекается другим тарифом'],
            ]);
        }

        // create or update rates
        $ratesList->start_at = Carbon::parse($data['start_at']);
        $ratesList->end_at = Carbon::parse($data['end_at']);
        $ratesList->excursion_id = $excursionId;
        $ratesList->save();

        $created = [];
        $changed = [];
        $unchanged = [];

        $rates = $ratesList->rates->keyBy('grade_id');
        $gradeKeys = $rates->pluck('grade_id')->toArray();

        foreach ($data['rates'] as $rate) {
            if (in_array($rate['grade_id'], $gradeKeys, true)) {
                // exists
                /** @var TicketRate $existing */
                $existing = $rates->where('grade_id', $rate['grade_id'])->first();
                $existing->base_price = $rate['base_price'];
                $existing->min_price = $rate['min_price'];
                $existing->max_price = $rate['max_price'];
                $existing->commission_type = $rate['commission_type'];
                $existing->commission_value = $rate['commission_value'];
                if ($existing->isDirty()) {
                    $existing->save();
                    $changed[] = $existing->id;
                } else {
                    $unchanged[] = $existing->id;
                }
            } else {
                // new
                /** @var TicketRate $new */
                $new = $ratesList->rates()->create([
                    'grade_id' => $rate['grade_id'],
                    'base_price' => $rate['base_price'],
                    'min_price' => $rate['min_price'],
                    'max_price' => $rate['max_price'],
                    'commission_type' => $rate['commission_type'],
                    'commission_value' => $rate['commission_value'],
                ]);
                $created[] = $new->id;
            }
        }

        // check if any rates has been removed
        $touched = array_merge($created, $changed, $unchanged);
        $deleted = $ratesList->rates()->whereNotIn('id', $touched)->pluck('id')->toArray();
        $ratesList->rates()->whereNotIn('id', $touched)->delete();

        $ratesList->refresh();

        return APIResponse::formSuccess($ratesList->wasRecentlyCreated ? 'Тариф добавлен' : 'Тариф изменён',
            [
                'rate' => $this->rateListToArray($ratesList),
                'debug' => [
                    'created' => $created,
                    'changed' => $changed,
                    'unchanged' => $unchanged,
                    'deleted' => $deleted,
                ],
            ]
        );
    }

    /**
     * Format rates list for output.
     *
     * @param TicketsRatesList $ratesList
     *
     * @return  array
     */
    protected function rateListToArray(TicketsRatesList $ratesList): array
    {
        return [
            'id' => $ratesList->id,
            'start_at' => $ratesList->start_at->format('d.m.Y'),
            'end_at' => $ratesList->end_at->format('d.m.Y'),
            'caption' => $ratesList->caption,
            'rates' => $ratesList->rates->map(static function (TicketRate $rate) {
                return [
                    'id' => $rate->id,
                    'rate_id' => $rate->rate_id,
                    'grade_id' => $rate->grade_id,
                    'base_price' => $rate->base_price,
                    'min_price' => $rate->min_price,
                    'max_price' => $rate->max_price,
                    'commission_type' => $rate->commission_type,
                    'commission_value' => $rate->commission_value,
                ];
            }),
        ];
    }
}
