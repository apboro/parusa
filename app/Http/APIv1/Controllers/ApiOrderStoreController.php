<?php

namespace App\Http\APIv1\Controllers;

use App\Actions\CreateOrderFromApi;
use App\Events\AstraMarineNewOrderEvent;
use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Http\APIResponse;
use App\Http\APIv1\Requests\ApiOrderStoreRequest;
use App\Http\Controllers\Controller;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Partner\Partner;
use App\Models\Sails\Trip;
use App\Models\Tickets\Ticket;
use Illuminate\Support\Facades\Log;

class ApiOrderStoreController extends Controller
{
    public function __invoke(ApiOrderStoreRequest $request)
    {

        try {
            /** @var Partner $partner */
            $partner = auth()->user();
            $trip = Trip::with('excursion')
                ->where('id', $request->trip_id)
                ->first();
            if (!$trip) {
                return ApiResponse::notFound('Рейс не найден');
            }
            $rateList = $trip->getRate();
            if (($trip->start_at < now() && $trip->excursion->is_single_ticket == 0)
                || ($trip->excursion->is_single_ticket != 0 && $trip->start_at < now() && !$trip->start_at->isCurrentDay())
                || !$rateList
                || !$trip->hasStatus(TripStatus::regular)
                || !$trip->hasStatus(TripSaleStatus::selling, 'sale_status_id')
            ) {
                return ApiResponse::notFound('Рейс не актуален');
            }
            $tickets = $request->tickets;
            if (empty($tickets)) {
                return ApiResponse::badRequest('Не передан массив билетов');
            }
            $ticketsLeft = $trip->tickets()->whereIn('status_id', TicketStatus::ticket_countable_statuses)->count();
            $ticketsNeed = count($tickets);
            if (($ticketsLeft + $ticketsNeed) > $trip->tickets_total) {
                return ApiResponse::error('Недостаточно билетов, осталось ' . $ticketsLeft);
            }
            foreach ($tickets as $ticket) {

                $rate = $rateList->rates()->where('grade_id', $ticket['grade_id'])->first();
                if (!$rate) {
                    return ApiResponse::badRequest('Неправильно указан grade_id для заказа билета - ' . $ticket['grade_id']);
                }

                if (!$rate->partner_price) {
                    return ApiResponse::error('Для данного grade_id не установлена цена');
                }

                $newTickets[] = Ticket::make([
                    'trip_id' => $request->trip_id,
                    'grade_id' => $ticket['grade_id'],
                    'status_id' => TicketStatus::api_reserved,
                    'provider_id' => $trip->excursion->provider_id,
                    'base_price' => $rate->partner_price,
                    'seat_id' => $request->seat_id,
                ]);
            }
            if (empty($newTickets)) {
                return ApiResponse::error('Не удалось оформить билеты');
            }
            $customerData['email'] = $request->client_email;
            $customerData['name'] = $request->client_name;
            $customerData['phone'] = $request->client_phone;
            $order = (new CreateOrderFromApi($partner))->execute($newTickets, $customerData);
            NewNevaTravelOrderEvent::dispatch($order);
            NewCityTourOrderEvent::dispatch($order);
            AstraMarineNewOrderEvent::dispatch($order);
            return ApiResponse::response(
                data: [
                    'order_id' => $order->id
                ],
                message: 'Заказ зарезервирован',
                unsetPayload: true);
        } catch (\Exception $e) {
            Log::channel('apiv1')->error($e->getMessage(). ' ' . $e->getFile(). ' ' . $e->getLine());
            return APIResponse::error($e->getMessage());
        }
    }
}
