<?php

namespace App\Http\Controllers\Showcase;

use App\Actions\CreateOrderFromShowcase;
use App\Events\AstraMarineNewOrderEvent;
use App\Events\NewCityTourOrderEvent;
use App\Events\NewNevaTravelOrderEvent;
use App\Exceptions\Tickets\NoTicketsForTripException;
use App\Exceptions\Tickets\TicketsValidationException;
use App\Exceptions\Tickets\WrongTicketsQuantityException;
use App\Http\APIResponse;
use App\Http\Controllers\ApiEditController;
use App\Http\Middleware\ExternalProtect;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\Sails\Trip;
use App\Models\Ships\Seats\TripSeat;
use App\Models\Tickets\Ticket;
use App\Models\Tickets\TicketRate;
use Carbon\Carbon;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use JsonException;

class ShowcaseOrderController extends ApiEditController
{
    /**
     * Create order for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function order(Request $request): JsonResponse
    {
        Hit::register(HitSource::showcase);
        $originalKey = $request->header(ExternalProtect::HEADER_NAME);

        try {
            $originalKey = $originalKey ? json_decode(Crypt::decrypt($originalKey), true, 512, JSON_THROW_ON_ERROR) : null;
        } catch (Exception $exception) {
            return response()->json(['message' => 'Ошибка сессии.'], 400);
        }

        if (!$request->data['email']){
            return APIResponse::error('Не указан email');
        }

        list($user, $domain) = explode('@', $request->data['email']);
        if (!checkdnsrr($domain, 'MX')) {
            return APIResponse::error('Указан несуществующий email');
        }

        /** @var ?Partner $partner */
        $partner = $originalKey['partner_id'] ? Partner::query()->where('id', $originalKey['partner_id'])->first() : null;
        $partnerId = $partner->id ?? null;
        $isPartnerSite = $originalKey['is_partner'];
        $media = $originalKey['media'] ?? null;
        $now = Carbon::now();

        /** @var Trip $trip */
        $tripQuery = Trip::query()
            ->where(function (Builder $trip) use ($now) {
                $trip->where('start_at', '>', $now)
                    ->orWhere(function (Builder $trip) use ($now) {
                        $trip->where('end_at', '>', $now)
                            ->whereHas('excursion', function (Builder $excursion) use ($now) {
                                $excursion->where('is_single_ticket', 1);
                            });
                    });
            })
            ->whereIn('status_id', [TripStatus::regular])
            ->whereIn('sale_status_id', [TripSaleStatus::selling])
            ->whereHas('excursion.ratesLists', function (Builder $query) use ($isPartnerSite) {
                $query
                    ->whereRaw('DATE(tickets_rates_list.start_at) <= DATE(trips.start_at)')
                    ->whereRaw('DATE(tickets_rates_list.end_at) >= DATE(trips.end_at)')
                    ->whereHas('rates', function (Builder $query) use ($isPartnerSite) {
                        $query->where('grade_id', '!=', TicketGrade::guide);
                        if ($isPartnerSite) {
                            $query->where('partner_price', '>', 0);
                        } else {
                            $query->where('base_price', '>', 0);
                        }
                    });
            });

        $trip = $tripQuery->clone()->where('id', $request->input('trip'))
            ->first();

        $backwardTrip = $tripQuery->clone()->where('id', $request->input('backwardTripId'))
            ->first();

        if ($trip === null) {
            return APIResponse::error('Нет продажи билетов на этот рейс.');
        }

        $flat = $request->input('data');
        $data = Arr::undot($flat);
        if (!$trip->additionalData?->with_seats) {
            $tickets = $this->createTickets($data, $trip, $backwardTrip, $isPartnerSite);
        } else {
            $tickets = $this->createTicketsWithScheme($data, $request, $trip);
        }

        if ($media === 'qr') {
            $orderType = OrderType::qr_code;
        } else if ($isPartnerSite) {
            $orderType = OrderType::partner_site;
        } else {
            $orderType = OrderType::site;
        }

        DB::transaction(static function () use ($data, $orderType, $tickets, $flat, $partnerId, $isPartnerSite, &$order) {
            // create order

            $order = (new CreateOrderFromShowcase())->execute($data, $orderType, $tickets['tickets'], $partnerId, $isPartnerSite === true, $flat['promocode'], $tickets['backwardTickets']);

            NewNevaTravelOrderEvent::dispatch($order);
            NewCityTourOrderEvent::dispatch($order);
            AstraMarineNewOrderEvent::dispatch($order);

        });

        $orderSecret = json_encode([
            'id' => $order->id,
            'ts' => Carbon::now(),
            'ip' => $request->ip(),
            'ref' => null,
        ], JSON_THROW_ON_ERROR);

        // clear media and partner cookie after successful order.
        $originalKey = [
            'ip' => $request->ip(),
        ];

        $secret = Crypt::encrypt($orderSecret);

        return response()->json([
            'payload' => [
                'order_id' => $order->id,
                'payment_page' => config('showcase.showcase_payment_page') . '?order=' . $secret,
                'order_secret' => $secret,
            ],
        ], 200, [ExternalProtect::HEADER_NAME => Crypt::encrypt(json_encode($originalKey, JSON_THROW_ON_ERROR))]);
    }

    /**
     * Cancel order for showcase application.
     *
     * @param Request $request
     *
     * @return JsonResponse
     * @throws JsonException
     */
    public function cancel(Request $request): JsonResponse
    {
        Hit::register(HitSource::showcase);
        $secret = $request->input('secret');

        try {
            $orderSecret = json_decode(Crypt::decrypt($secret), true, 512, JSON_THROW_ON_ERROR);
        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Неверные данные заказа.',
                'error' => $exception->getMessage(),
            ], 400);
        }

        /** @var Order|null $order */
        $order = Order::query()->with('status')->where('id', $orderSecret['id'])->first();

        if ($order === null) {
            return APIResponse::error('Заказ не найден.');
        }

        $now = Carbon::now();
        $expires = Carbon::parse($orderSecret['ts'])->setTimezone($now->timezone)
            ->addMinutes(config('showcase.showcase_order_lifetime'));

        if ($expires < $now) {
            return APIResponse::success('Время, отведенное на оплату заказа истекло, заказ расформирован.');
        }

        try {
            DB::transaction(static function () use ($order) {
                $order->setStatus(OrderStatus::showcase_canceled);
                $order->tickets->map(function (Ticket $ticket) {
                    $ticket->setStatus(TicketStatus::showcase_canceled);
                });
            });
        } catch (Exception $exception) {
            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ расформирован.');
    }

    private function createTickets(array $data, Trip $trip, ?Trip $backwardTrip, bool $isPartnerSite)
    {
        $count = count($data['rate'] ?? []);
        if ($count === 0) {
            throw new WrongTicketsQuantityException();
        }
        $grades = array_keys($data['rate']);
        $count = 0;

        $rules = ['name' => 'required', 'email' => 'required|email|bail', 'phone' => 'required'];
        $titles = ['name' => 'Имя', 'email' => 'Email', 'phone' => 'Телефон'];

        foreach ($grades as $grade) {
            $count += $data['rate'][$grade]['quantity'];
            $rules["rate.$grade.quantity"] = 'nullable|integer|min:0|bail';
            $titles["rate.$grade.quantity"] = 'Количество';
        }

        if ($errors = $this->validate($data, $rules, $titles)) {
            throw new TicketsValidationException($errors);
        }

        if ($count === 0) {
            $errors = [];
            foreach ($grades as $grade) {
                $errors["rate.$grade.quantity"] = ['Не выбрано количество'];
            }
            throw new TicketsValidationException($errors);
        }

        $rates = $trip->excursion->rateForDate($trip->start_at);
        if ($rates === null) {
            throw new NoTicketsForTripException();
        }

        $tickets = [];
        $backwardTickets = [];
        foreach ($data['rate'] as $gradeId => $grade) {
            if ($grade['quantity'] > 0) {
                // get rate
                /** @var TicketRate $rate */
                $rate = $rates->rates->where('grade_id', $gradeId)->first();
                if ($rate === null) {
                    throw new NoTicketsForTripException();
                }
                for ($i = 1; $i <= $grade['quantity']; $i++) {
                    $ticket = new Ticket([
                        'trip_id' => $trip->id,
                        'grade_id' => $gradeId,
                        'status_id' => TicketStatus::showcase_creating,
                        'base_price' => $isPartnerSite ? $rate->partner_price : $rate->site_price ?? $rate->base_price,
                        'provider_id' => $trip->provider_id,
                    ]);
                    if ($backwardTrip) {
                        $backwardTicket = new Ticket([
                            'trip_id' => $backwardTrip->id,
                            'grade_id' => $gradeId,
                            'status_id' => TicketStatus::showcase_creating,
                            'base_price' => $rate->backward_price_type === 'fixed' ? $rate->backward_price_value : $rate->base_price * ($rate->backward_price_value / 100),
                            'provider_id' => $trip->provider_id,
                        ]);
                        $backwardTickets[] = $backwardTicket;
                    }
                    $tickets[] = $ticket;
                }
            }
        }
        return ['tickets' => $tickets, 'backwardTickets' => $backwardTickets];
    }

    private function createTicketsWithScheme(array $data, Request $request, Trip $trip)
    {
        $rules = ['name' => 'required', 'email' => 'required|email|bail', 'phone' => 'required'];
        $titles = ['name' => 'Имя', 'email' => 'Email', 'phone' => 'Телефон'];

        if ($errors = $this->validate($data, $rules, $titles)) {
            throw new TicketsValidationException($errors);
        }

        $rates = $trip->excursion->rateForDate($trip->start_at);
        if ($rates === null) {
            throw new NoTicketsForTripException();
        }

        foreach ($request->tickets as $showcaseTicket) {

            TripSeat::query()
                ->updateOrCreate(['trip_id' => $trip->id, 'seat_id' => $showcaseTicket['seatId']],
                    ['status_id' => SeatStatus::occupied]);

            $ticket = new Ticket([
                'trip_id' => $trip->id,
                'grade_id' => $showcaseTicket['grade']['id'],
                'status_id' => TicketStatus::showcase_creating,
                'base_price' => $showcaseTicket['price'],
                'seat_id' => $showcaseTicket['seatId'],
                'provider_id' => $trip->provider_id,
            ]);

            if ($showcaseTicket['menu']) {
                $ticket->menu_id = $showcaseTicket['menu']['id'] ?? null;
            }
            $tickets[] = $ticket;
        }
        return ['tickets' => $tickets ?? [], 'backwardTickets' => []];
    }

}
