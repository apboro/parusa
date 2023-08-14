<?php

namespace App\NevaTravel;

use App\Models\Dictionaries\TicketStatus;
use App\Models\Order\Order;
use Facade\FlareClient\Api;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class NevaTravelRepository
{
    private ApiClientProvider $apiClient;

    public function __construct()
    {
        $this->apiClient = new ApiClientProvider();
    }

    public function getPiersInfo(array $query = []): array
    {
        return $this->apiClient->get('get_piers_info', $query);
    }

    public function getShipsInfo(array $query = []): array
    {
        return $this->apiClient->get('get_ships_info', $query);
    }

    public function getProgramsInfo(array $query = []): array
    {
        return $this->apiClient->get('get_programs_info', $query);
    }

    public function getCruisesInfo(array $query = []): array
    {
        return $this->apiClient->get('get_cruises_info', $query);
    }

    public function getSchedule(array $query = [])
    {
        return $this->apiClient->get('get_schedule', $query);
    }

    public function makeOrder(Order $order): ?array
    {
        $query = $this->makeNevaOrderFromParusaOrder($order);
        if ($query) {
            return $this->apiClient->post('request_ticket_order', $query);
        } else {
            return null;
        }
    }

    public function makeComboOrder(Order $order): ?array
    {
        $query = $this->makeNevaComboTemplateOrderFromParusaOrder($order);
        if ($query) {
            return $this->apiClient->post('request_combo_template_order', $query);
        } else {
            return null;
        }
    }

    public function approveOrder(string $query = ''): array
    {
        return $this->apiClient->post('approve_order?order_id=' . $query);
    }

    public function cancelOrder(array $query = []): array
    {
        Log::channel('neva')->info('cancelOrder Query', [$query]);
        $result = $this->apiClient->post('cancel_order', $query);
        Log::channel('neva')->info('Neva cancel order result:', [$result]);
        return $result;
    }

    public function getOrderInfo(string $query = ''): array
    {
        return $this->apiClient->post('get_order_info?order_id=' . $query);
    }

    public function commentOrder(array $query = []): array
    {
        return $this->apiClient->post('comment_order', $query);
    }

    public function makeComboTemplateOrder(array $query = []): array
    {
        return $this->apiClient->post('request_combo_template_order', $query);
    }


    public function makeNevaOrderFromParusaOrder(Order $order): ?array
    {
        $tickets = $order->tickets()
            ->whereIn('status_id', TicketStatus::ticket_countable_statuses)
            ->where('provider_id', 10)
            ->get();
        if ($tickets->isNotEmpty()) {
            foreach ($tickets as $ticket) {
                $ticket_list[] =
                    [
                        'departure_point_id' => $ticket->trip->additionalData->provider_trip_id,
                        'program_price_id' => $ticket->trip->additionalData->provider_price_id,
                        'ticket_category' => $ticket->grade->external_grade_name,
                        'purchase_price' => $ticket->base_price,
                        'qty' => 1
                    ];
            }
            $params = [
                'ticket_list' => $ticket_list,
                'hide_ticket_price' => true,
                'client_name' => $order->name,
                'client_phone' => $order->phone,
                'client_email' => $order->email,
                'comment' => ''
            ];

            return $params;
        } else {
            return null;
        }
    }


    //Для оформления комбо заказа нужно отправить айди рейсов Невы прямого-обратного билетов
    public function makeNevaComboTemplateOrderFromParusaOrder(Order $order)
    {
        $tickets = $order->tickets()
            ->with(['trip'])
            ->whereIn('status_id', TicketStatus::ticket_countable_statuses)
            ->where('provider_id', 10)
            ->get();

        $combos = collect($this->getCombosInfo()['body']);

        $mainTripExternalId = $tickets[0]->trip->additionalData->provider_trip_id;
        $mainExcursionExternalId = $tickets[0]->trip->excursion->additionalData->provider_excursion_id;
        $backTripExternalId = $order->backwards[0]->ticket->trip->additionalData->provider_trip_id;
        $backExcursionExternalId = $order->backwards[0]->ticket->trip->excursion->additionalData->provider_excursion_id;

        $comboExcursion = $combos->first(function ($element) use ($backExcursionExternalId, $mainExcursionExternalId) {
            $ticketExcursions = [
                $mainExcursionExternalId,
                $backExcursionExternalId
            ];
            return Arr::sortRecursive($ticketExcursions) === Arr::sortRecursive($element['program_ids']);
        });

        foreach ($tickets as $ticket) {
            $ticketsList[] = [
                "ticket_category" => $ticket->grade->external_grade_name,
                "purchase_price" => $ticket->base_price,
                "qty" => 1
            ];
        }

        $combosPriceId = $comboExcursion['template_prices_table'][0]['id'];

        return [
            "content" => [
                "combo_price_id" => $combosPriceId,
                "program_list" => [
                    [
                        "program_id" => $mainExcursionExternalId,
                        "route" => [
                            "departure_point_id" => $mainTripExternalId,
                        ],
                    ],
                    [
                        "program_id" => $backExcursionExternalId,
                        "route" => [
                            "departure_point_id" => $backTripExternalId,
                        ],
                    ]
                ],
                "ticket_list" => $ticketsList
            ],
            'hide_ticket_price' => true,
            'client_name' => $order->name,
            'client_phone' => $order->phone,
            'client_email' => $order->email,
            'comment' => ''
        ];
    }

    public function getCombosInfo(): array
    {
        return $this->apiClient->get('get_combos_info');
    }

    public function checkCanOrderTickets(string $trip_external_id)
    {
        $trip = $this->getCruisesInfo(['point_id' => $trip_external_id]);

        //many (50), less_than_10 (есть, менее 10), less_than_3 (есть, менее 3), none (нет мест)
        $seatsQuantityString = $trip['body'][0]['default_arrival']['prices_table'][0]['available_seats'];
        return $this->convertSeatsToInt($seatsQuantityString);
    }


    public static function convertSeatsToInt($seatsQuantityString, int $shipCapacity): int
    {
        return match ($seatsQuantityString) {
            'many' => $shipCapacity,
            'less_than_10' => 10,
            'less_than_3' => 3,
            'none' => 0,
            default => -1,
        };
    }


}

