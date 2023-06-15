<?php

namespace App\Helpers;

use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketRate;

class Promocode
{
    public static function calc(?string $promocode, array $tickets, bool $isPartnerSite): array
    {
        $tripID = $tickets[0]['trip_id'];

        $tickets_count = array_column($tickets, 'quantity');
        $tickets_count = array_sum($tickets_count);

        $full_price = 0;
        $discount_price = 0;
        $discounted = 0;

        if ($tickets_count < 1) {
            return [
                'full_price' => $full_price,
                'discount_price' => $discount_price,
                'discounted' => $discounted,
                'status' => false,
                'message' => 'В заказе нет билетов.'
            ];
        }

        $trip = Trip::find($tripID);
        $rates = $trip->excursion->rateForDate($trip->start_at);
        if ($rates === null) {
            return [
                'full_price' => $full_price,
                'discount_price' => $discount_price,
                'discounted' => $discounted,
                'status' => false,
                'message' => 'Нет продажи билетов на этот рейс.'
            ];
        }

        $arrTickets = [];
        foreach ($tickets as $grade) {
            if ($grade['quantity'] > 0) {
                // get rate
                /** @var TicketRate $rate */
                $rate = $rates->rates->where('grade_id', $grade['grade_id'])->first();
                if ($rate === null) {
                    return [
                        'full_price' => $full_price,
                        'discount_price' => $discount_price,
                        'discounted' => $discounted,
                        'status' => false,
                        'message' => 'Нет продажи билетов на этот рейс.'
                    ];
                }
                for ($i = 1; $i <= $grade['quantity']; $i++) {
                    $ticket = new Ticket([
                        'trip_id' => $trip->id,
                        'grade_id' => $grade['grade_id'],
                        'status_id' => TicketStatus::showcase_creating,
                        'base_price' => $isPartnerSite ? $rate->base_price : $rate->site_price,
                        'neva_travel_ticket' => $trip->source === 'NevaTravelApi',
                    ]);
                    $arrTickets[] = $ticket;
                }
            }
        }

        $prices = array_column($arrTickets, 'base_price');
        $full_price = array_sum($prices);

        $code = mb_strtoupper($promocode);
        if ($code === null || $code === '') {
            return [
                'full_price' => $full_price,
                'discount_price' => $full_price,
                'discounted' => 0,
                'status' => true,
                'message' => ''
            ];
        }

        /** @var \App\Models\PromoCode\PromoCode $promoCode */
        if (null === ($promoCode = \App\Models\PromoCode\PromoCode::query()->where('code', $code)->where('status_id', PromoCodeStatus::active)->first())) {
            return [
                'full_price' => $full_price,
                'discount_price' => $full_price,
                'discounted' => 0,
                'status' => false,
                'message' => 'Введенный вами промокод не действителен.'
            ];
        }

        if (empty($promoCode->excursions->where('id', $trip->excursion_id)->first())) {
            return [
                'full_price' => $full_price,
                'discount_price' => $full_price,
                'discounted' => 0,
                'status' => false,
                'message' => 'Действие промокода не распространяется на выбранную экскурсию.'
            ];
        }

        return [
            'full_price' => $full_price,
            'discount_price' => $full_price - $promoCode->amount,
            'discounted' => $promoCode->amount,
            'status' => true,
            'message' => ''
        ];
    }
}
