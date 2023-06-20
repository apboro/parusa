<?php

namespace App\Helpers;

use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\Sails\Trip;
use App\Models\Tickets\TicketRate;

class Promocode
{
    public static function calc(?string $promocode, array $tickets, ?int $partnerID = null): array
    {
        $full_price = 0;
        $discount_price = 0;
        $discounted = 0;
        $code = mb_strtoupper($promocode);
        /** @var \App\Models\PromoCode\PromoCode $promoCode */
        $promoCode = \App\Models\PromoCode\PromoCode::query()
            ->with('excursions')
            ->where('code', $code)
            ->where('status_id', PromoCodeStatus::active)
            ->first();

        $tickets = array_filter($tickets, static function ($ticket) {
            return $ticket['quantity'] && $ticket['quantity'] !== 0;
        });

        if (empty($tickets)) {
            return [
                'full_price' => $full_price,
                'discount_price' => $discount_price,
                'discounted' => $discounted,
                'status' => $promoCode !== null,
                'message' => 'В заказе нет билетов.',
            ];
        }

        $tripIDs = array_unique(array_column($tickets, 'trip_id'));

        $trips = Trip::saleTripQuery($partnerID === null)
            ->whereIn('id', $tripIDs)
            ->get();

        $promocodeApplicable = false;

        foreach ($tickets as $ticket) {
            // {trip_id: 17433, grade_id: 2, quantity: 0}

            /** @var Trip|null $trip */
            $trip = $trips->where('id', $ticket['trip_id'])->first();
            if ($trip === null) {
                return [
                    'full_price' => $full_price,
                    'discount_price' => $discount_price,
                    'discounted' => $discounted,
                    'status' => $promoCode !== null,
                    'message' => 'Рейс не найден.',
                ];
            }

            if ($promoCode && $promoCode->excursions->where('id', $trip->excursion_id)->count() > 0) {
                $promocodeApplicable = true;
            }

            $rates = $trip->excursion->rateForDate($trip->start_at);

            if ($rates === null) {
                return [
                    'full_price' => $full_price,
                    'discount_price' => $discount_price,
                    'discounted' => $discounted,
                    'status' => $promoCode !== null,
                    'message' => 'Нет продажи билетов на этот день.',
                ];
            }

            /** @var TicketRate|null $rate */
            $rate = $rates->rates->where('grade_id', $ticket['grade_id'])->first();

            if ($rate === null) {
                return [
                    'full_price' => $full_price,
                    'discount_price' => $discount_price,
                    'discounted' => $discounted,
                    'status' => $promoCode !== null,
                    'message' => 'Нет продажи билетов на этот день.',
                ];
            }

            $base_price = $partnerID === null ? ($rate->site_price ?? $rate->base_price) : $rate->base_price;

            $full_price += $base_price * ($ticket['quantity'] ?? 0);
            // Refactor on need $discount_price += $base_price;
            // Refactor on need $discounted += 0;
        }

        if ($promoCode === null || !$promocodeApplicable) {
            return [
                'full_price' => $full_price,
                'discount_price' => $full_price,
                'discounted' => 0,
                'status' => false,
                'message' => $promoCode === null ? 'Введенный вами промокод не действителен.' : 'Действие промокода не распространяется на выбранную экскурсию.',
            ];
        }

        return [
            'full_price' => $full_price,
            'discount_price' => $full_price - $promoCode->amount, // Refactor on need
            'discounted' => $promoCode->amount, // Refactor on need
            'status' => true,
            'message' => null,
        ];
    }
}
