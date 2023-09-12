<?php

namespace App\Http\Controllers\API\Rates;

use App\Models\Dictionaries\TicketGrade;
use App\Models\Tickets\TicketPartnerRate;
use App\Models\Tickets\TicketRate;
use App\Models\Tickets\TicketsRatesList;
use Carbon\Carbon;

trait RateToArray
{
    /**
     * Format and filter ticket rates.
     *
     * rate_list
     * rate_list.rates
     * rate_list.rates.grade
     * rate_list.rates.partnerRates
     *
     * @param TicketsRatesList $list
     * @param bool $zeroPrice
     * @param bool $sitePrice
     *
     * @return  array
     */
    protected function rateToArray(TicketsRatesList $list, bool $zeroPrice = false, bool $sitePrice = false): array
    {
        $now = Carbon::now()->startOfDay();
        $overridden = false;
        $rates = $list->rates;

        // Filter rates with zero price
        if (!$zeroPrice) {
            $rates = $rates->filter(function (TicketRate $rate) {
                return $rate->grade_id === TicketGrade::guide || $rate->base_price !== 0;
            });
        }

        $rates = $rates->map(function (TicketRate $rate) use (&$overridden, $sitePrice) {
            /** @var TicketPartnerRate $partnerRate */
            $partnerRate = $rate->partnerRates && $rate->partnerRates->count() === 1 ? $rate->partnerRates[0] : null;
            $overridden = $overridden || $partnerRate !== null;

            $r = [
                'id' => $rate->id,
                'grade_id' => $rate->grade_id,
                'base_price' => $rate->base_price,
                'max_price' => $rate->max_price,
                'min_price' => $rate->min_price,
                'commission_type' => $rate->commission_type,
                'commission_value' => $rate->commission_value,
                'partner_commission_type' => $partnerRate->commission_type ?? null,
                'partner_commission_value' => $partnerRate->commission_value ?? null,
                'backward_price_type' => $rate->backward_price_type,
                'backward_price_value' => $rate->backward_price_value,
                'partner_price' => $rate->partner_price
            ];
            if ($sitePrice) {
                $r['site_price'] = $rate->site_price;
            }
            return $r;
        });

        return [
            'id' => $list->id,
            'current' => $list->start_at <= $now && $now <= $list->end_at,
            'archive' => $list->end_at < $now,
            'excursion' => $list->excursion->name,
            'excursion_id' => $list->excursion_id,
            'start' => $list->start_at->format('d.m.Y'),
            'end' => $list->end_at->format('d.m.Y'),
            'start_at' => $list->start_at->format('Y-m-d'),
            'end_at' => $list->end_at->format('Y-m-d'),
            'caption' => $list->caption,
            'overridden' => $overridden,
            'rates' => array_values($rates->toArray()),
        ];
    }
}
