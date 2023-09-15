<?php

namespace App\Http\Controllers\API\Order;

use App\Events\CityTourCancelOrderEvent;
use App\Events\NevaTravelCancelOrderEvent;
use App\Http\APIResponse;
use App\Http\Controllers\ApiController;
use App\Models\Account\AccountTransaction;
use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\AccountTransactionType;
use App\Models\Dictionaries\HitSource;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Hit\Hit;
use App\Models\Order\Order;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;
use App\Services\NevaTravel\NevaOrder;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class OrderReserveController extends ApiController
{
    /**
     * Remove reserve.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function cancel(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        if ($current->isRepresentative()) {
            Hit::register(HitSource::partner);
        } else if ($current->isStaffTerminal()) {
            Hit::register(HitSource::terminal);
        } else {
            Hit::register(HitSource::admin);
        }

        /** @var ?Order $order */
        $order = $this->getOrder($request);

        if ($order === null) {
            return APIResponse::error('Бронь не найдена');
        }

        // delete all tickets
        DB::transaction(static function () use ($order) {

            NevaTravelCancelOrderEvent::dispatch($order);
            CityTourCancelOrderEvent::dispatch($order);

            foreach ($order->tickets as $ticket) {
                $ticket->setStatus(TicketStatus::partner_reserve_canceled);
            }
            $order->setStatus(OrderStatus::partner_reserve_canceled);
        });

        return APIResponse::success('Бронь аннулирована.');
    }

    /**
     * Remove ticket from reserve.
     *
     * @param Request $request
     *
     * @return  JsonResponse
     */
    public function remove(Request $request): JsonResponse
    {
        $current = Currents::get($request);

        if ($current->isRepresentative()) {
            Hit::register(HitSource::partner);
        } else if ($current->isStaffTerminal()) {
            Hit::register(HitSource::terminal);
        } else {
            Hit::register(HitSource::admin);
        }

        /** @var Order|null $order */
        $order = $this->getOrder($request);

        if ($order === null) {
            return APIResponse::error('Бронь не найдена');
        }

        /** @var Ticket $ticket */
        $ticket = Ticket::query()->where([
            'id' => $request->input('ticket_id'),
            'order_id' => $order->id,
        ])->first();

        if ($ticket === null || !$ticket->hasStatus(TicketStatus::partner_reserve)) {
            return APIResponse::error('Билет не найден или не находится в брони.');
        }

        // delete ticket
        $ticket->setStatus(TicketStatus::partner_reserve_canceled);

        // delete order if no tickets
        $hasActual = false;
        $order->loadMissing('tickets');
        foreach ($order->tickets as $ticket) {
            if (!$ticket->hasStatus(TicketStatus::partner_reserve_canceled)) {
                $hasActual = true;
                break;
            }
        }

        if (!$hasActual) {
            $order->setStatus(OrderStatus::partner_reserve_canceled);
        }
        return APIResponse::success('Билет удалён из брони.' . (!$hasActual ? ' Бронь расформирована.' : ''), [
            'reserve_cancelled' => !$hasActual,
        ]);
    }

    /**
     * Make order from reserve and pay. For partners.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function partnerOrder(Request $request): JsonResponse
    {
        Hit::register(HitSource::partner);
        /** @var ?Order $order */
        $order = $this->getOrder($request);

        if ($order === null) {
            return APIResponse::error('Бронь не найдена');
        }

        $order->loadMissing('tickets');

        $current = Currents::get($request);

        try {
            DB::transaction(static function () use ($order, $current) {
                // add transaction first
                $current->partner()->account->attachTransaction(new AccountTransaction([
                    'type_id' => AccountTransactionType::tickets_buy,
                    'status_id' => AccountTransactionStatus::accepted,
                    'timestamp' => Carbon::now(),
                    'amount' => $order->total(),
                    'order_id' => $order->id,
                    'committer_id' => $current->positionId(),
                ]));

                // update order statuses
                foreach ($order->tickets as $ticket) {
                    /** @var Ticket $ticket */
                    $ticket->setStatus(TicketStatus::partner_paid);
                }
                $order->setStatus(OrderStatus::partner_paid);
            });

            // pay commissions
            $order->payCommissions();

        } catch (Exception $exception) {

            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ оплачен.');
    }

    /**
     * Make order from reserve and pay. Terminal users.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function terminalOrder(Request $request): JsonResponse
    {
        Hit::register(HitSource::terminal);

        $current = Currents::get($request);

        if (Order::query()->where(['terminal_position_id' => $current->positionId(), 'terminal_id' => $current->terminalId()])
                ->whereIn('status_id', OrderStatus::terminal_processing_statuses)
                ->count() > 0
        ) {
            return APIResponse::error('Другой заказ уже находится в оформлении.');
        }

        /** @var ?Order $order */
        $order = $this->getOrder($request);

        if ($order === null) {
            return APIResponse::error('Бронь не найдена');
        }

        $order->loadMissing('tickets');

        try {
            DB::transaction(static function () use ($order, $current) {
                // update order statuses
                foreach ($order->tickets as $ticket) {
                    /** @var Ticket $ticket */
                    $ticket->setStatus(TicketStatus::terminal_creating_from_reserve);
                }
                $order->terminal_id = $current->terminalId();
                $order->terminal_position_id = $current->positionId();
                $order->setStatus(OrderStatus::terminal_creating_from_reserve, false);
                $order->save();
            });

        } catch (Exception $exception) {

            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ сформирован.');
    }

    /**
     * Get requested reserve order.
     *
     * @param Request $request
     *
     * @return  Order|null
     */
    protected function getOrder(Request $request): ?Order
    {
        $current = Currents::get($request);

        $query = Order::query()
            ->where('id', $request->input('id'))
            ->whereIn('status_id', OrderStatus::order_reserved_statuses);

        if ($current->isRepresentative()) {
            $query->where('partner_id', $current->partnerId());
        } else if (!($current->isStaffAdmin() || $current->isStaffOfficeManager() || $current->isStaffAccountant()) && !$current->isStaffTerminal()) {
            return null;
        }

        /** @var Order|null $order */
        /** @noinspection OneTimeUseVariablesInspection */
        $order = $query->first();

        return $order;
    }
}
