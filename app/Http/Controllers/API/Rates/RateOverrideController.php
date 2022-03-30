<?php

namespace App\Http\Controllers\API\Rates;

use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Models\Excursions\Excursion;
use App\Models\Partner\Partner;
use App\Models\Tickets\TicketPartnerRate;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class RateOverrideController extends ApiEditController
{
    use RateToArray;

    /**
     * Update rate.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function override(Request $request): JsonResponse
    {
        if (null === ($partnerId = $request->input('partner_id')) || null === ($partner = Partner::query()->where('id', $partnerId)->first())) {
            return APIResponse::notFound('Партнёр не найден');
        }

        if (null === ($excursionId = $request->input('excursion_id')) || null === (Excursion::query()->where('id', $excursionId)->first())) {
            return APIResponse::notFound('Экскурсия не найдена');
        }

        if (null === ($rateId = $request->input('rate_id')) || null === ($ratesList = TicketsRatesList::query()->where('id', $rateId)->first())) {
            return APIResponse::notFound('Тариф не найден');
        }

        /** @var TicketsRatesList $ratesList */
        /** @var Partner $partner */

        $flat = $request->input('data');
        $data = Arr::undot($flat);

        // make dynamic validation rules and check
        $count = count($data['rates'] ?? []);
        $rules = [];
        $titles = [];

        for ($i = 0; $i < $count; $i++) {
            $rules["rates.$i.partner_commission_type"] = 'nullable';
            $rules["rates.$i.partner_commission_value"] = 'nullable|integer|min:0|bail';
            $titles["rates.$i.partner_commission_type"] = 'Тип';
            $titles["rates.$i.partner_commission_value"] = 'Комиссия';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            return APIResponse::validationError($errors);
        }

        $ratesList->loadMissing('rates');

        $created = [];
        $changed = [];
        $unchanged = [];
        $missing = [];
        $deleted = [];

        $rates = $ratesList->rates->keyBy('grade_id');
        $gradeKeys = $rates->pluck('grade_id')->toArray();

        // iterate received rates
        foreach ($data['rates'] as $rate) {

            // if received rate grade exists in ticket rate
            if (in_array($rate['grade_id'], $gradeKeys, true)) {

                $createdPartnerRate = [];
                $changedPartnerRate = [];
                $unchangedPartnerRate = [];

                /** @var TicketRate $ticketRate */
                $ticketRate = $rates->where('grade_id', $rate['grade_id'])->first();

                // if override for partner (if unset, will be deleted later)
                if ($rate['partner_commission_type'] !== null) {

                    // get already overridden parameter
                    /** @var TicketPartnerRate $partnerRate */
                    $partnerRate = $ticketRate->partnerRates()->where('partner_id', $partner->id)->first();

                    // if it exists
                    if ($partnerRate !== null) {

                        // set parameters
                        $partnerRate->commission_type = $rate['partner_commission_type'];
                        $partnerRate->commission_value = $rate['partner_commission_value'] ?? 0;

                        // and check if them are changed
                        if ($partnerRate->isDirty()) {
                            $partnerRate->save();
                            $changedPartnerRate[] = $partnerRate->id;
                        } else {
                            $unchangedPartnerRate[] = $partnerRate->id;
                        }

                    } else {
                        // if it not exists - create
                        /** @var TicketPartnerRate $partnerRate */
                        $partnerRate = $ticketRate->partnerRates()->create(['partner_id' => $partner->id]);
                        $partnerRate->commission_type = $rate['partner_commission_type'];
                        $partnerRate->commission_value = $rate['partner_commission_value'] ?? 0;
                        $partnerRate->save();
                        $createdPartnerRate[] = $partnerRate->id;
                    }
                }

                // next get touched records and delete others
                $touched = array_merge($createdPartnerRate, $changedPartnerRate, $unchangedPartnerRate);
                $ticketRate->partnerRates()->where('partner_id', $partner->id)->first();

                $deletedPartnerRate = $ticketRate->partnerRates()
                    ->where('partner_id', $partner->id)
                    ->whereNotIn('id', $touched)
                    ->pluck('id')->toArray();
                $ticketRate->partnerRates()->whereIn('id', $deletedPartnerRate)->delete();

                $created[] = $createdPartnerRate;
                $changed[] = $changedPartnerRate;
                $unchanged[] = $unchangedPartnerRate;
                $deleted[] = $deletedPartnerRate;

            } else {
                // received rate grade not exists in ticket rate
                $missing[] = $rate;
            }
        }

        // reload rate list
        /** @var TicketsRatesList $ratesList */
        $ratesList = TicketsRatesList::query()
            ->where('id', $rateId)
            ->with('rates')
            ->with('rates.partnerRates', static function (HasMany $query) use ($partner) {
                $query->where('partner_id', $partner->id);
            })->first();

        return APIResponse::success('Специальные условия обновлены',
            [
                'rate' => $this->rateToArray($ratesList),
                'excursion_id' => $excursionId,
                'debug' => [
                    'created' => $created,
                    'changed' => $changed,
                    'unchanged' => $unchanged,
                    'deleted' => $deleted,
                    'missing' => $missing,
                ],
            ]
        );
    }

//    /**
//     * Format rates list for output.
//     *
//     * @param TicketsRatesList $ratesList
//     *
//     * @return  array
//     */
//    protected function rateListToArray(TicketsRatesList $ratesList): array
//    {
//        $overridden = false;
//
//        $list = [
//            'id' => $ratesList->id,
//            'start_at' => $ratesList->start_at->format('d.m.Y'),
//            'end_at' => $ratesList->end_at->format('d.m.Y'),
//            'caption' => $ratesList->caption,
//            'rates' => $ratesList->rates->map(static function (TicketRate $rate) use (&$overridden) {
//                /** @var TicketPartnerRate $partnerRate */
//                $partnerRate = $rate->partnerRates->count() === 1 ? $rate->partnerRates[0] : null;
//                $overridden = $overridden || $partnerRate !== null;
//
//                return [
//                    'id' => $rate->id,
//                    'rate_id' => $rate->rate_id,
//                    'grade_id' => $rate->grade_id,
//                    'base_price' => $rate->base_price,
//                    'min_price' => $rate->min_price,
//                    'max_price' => $rate->max_price,
//                    'commission_type' => $rate->commission_type,
//                    'commission_value' => $rate->commission_value,
//                    'partner_commission_type' => $partnerRate->commission_type ?? null,
//                    'partner_commission_value' => $partnerRate->commission_value ?? null,
//                ];
//            }),
//        ];
//
//        $list['overridden'] = $overridden;
//
//        return $list;
//    }
}
