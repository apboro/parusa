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

class OrderInstantPayController extends ApiController
{


    /**
     * Get order and set status paid. For promoters with self terminals.
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function pay(Request $request): JsonResponse
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
                ]), true);

                // update order statuses
                foreach ($order->tickets as $ticket) {
                    /** @var Ticket $ticket */
                    $ticket->setStatus(TicketStatus::promoter_paid);
                }
                $order->setStatus(OrderStatus::promoter_paid);
            });

            // pay commissions
            $order->payCommissions();

        } catch (Exception $exception) {

            return APIResponse::error($exception->getMessage());
        }

        return APIResponse::success('Заказ оплачен.');
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
        return Order::query()
            ->where('id', $request->input('id'))
            ->whereIn('status_id', [OrderStatus::promoter_wait_for_pay])->first();
    }
}
