<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongOrderException;
use App\Models\BackwardTicket;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\OrderType;
use App\Models\Dictionaries\Provider;
use App\Models\Dictionaries\TicketGrade;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Order\Order;
use App\Models\Partner\Partner;
use App\Models\Tickets\TicketRate;
use App\Models\User\Helpers\Currents;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\DB;

class CreateOrderFromApi
{

    public function __construct(private readonly Partner $partner)
    {

    }

    public function execute(array $tickets, array $customerData)
    {
        // prepare order
        $order = new Order();
        $order->setStatus(OrderStatus::api_reserved, false);
        $order->setType(OrderType::api_sale, false);
        $order->partner_id = $this->partner->id;
        $order->email = $customerData['email'];
        $order->name = $customerData['name'];
        $order->phone = $customerData['phone'];

        try {
            DB::transaction(static function () use (&$order, $tickets) {
                $order->save();

                foreach ($tickets as $ticket) {
                    $ticket->order_id = $order->id;
                    $ticket->save();
                }
            });
        } catch (Exception $exception) {
            throw new WrongOrderException($exception->getMessage());
        }

        return $order;
    }

}
