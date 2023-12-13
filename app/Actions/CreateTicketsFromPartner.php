<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongOrderStatusException;
use App\Exceptions\Tickets\WrongTicketCreatorException;
use App\Http\APIResponse;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Ships\Seats\TripSeat;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;

class CreateTicketsFromPartner
{
    public function __construct(private readonly Currents $current)
    {
    }

    public function execute(array $data, $orderStatus): array
    {
        $totalAmount = 0;
        foreach ($data['tickets'] as $id => $quantity) {
            if ($quantity['quantity'] > 0) {
                $ordering = PositionOrderingTicket::query()
                    ->with(['trip', 'trip.excursion'])
                    ->where(['id' => $id, 'position_id' => $this->current->positionId(), 'terminal_id' => null])
                    ->first();
                if (!$ordering)
                    throw new WrongTicketCreatorException();

                $ticketStatus = match ($orderStatus) {
                    OrderStatus::partner_reserve => TicketStatus::partner_reserve,
                    OrderStatus::partner_paid => $ordering->trip->excursion->is_single_ticket ? TicketStatus::partner_paid_single : TicketStatus::partner_paid,
                    default => throw new WrongOrderStatusException(),
                };

                if ($ordering->seat) {
                    TripSeat::query()
                        ->updateOrCreate(['trip_id' => $ordering->trip->id, 'seat_id' => $ordering->seat->id],
                            ['status_id' => SeatStatus::occupied]);
                }

                for ($i = 1; $i <= $quantity['quantity']; $i++) {
                    /** @var PositionOrderingTicket $ordering */
                    $ticket = new Ticket([
                        'trip_id' => $ordering->trip_id,
                        'grade_id' => $ordering->grade_id,
                        'status_id' => $ticketStatus,
                        'provider_id' => $ordering->trip->provider_id,
                        'seat_id' => $ordering->seat_id,
                    ]);

                    $ticket->base_price = $ordering->getPartnerPrice() ?? $ordering->getPrice();
                    $ticket->cart_ticket_id = $ordering->id;
                    $ticket->cart_parent_ticket_id = $ordering->parent_ticket_id;
                    $ticket->backward_price = $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : null;
                    $ticket->menu_id = $ordering->menu_id ?? null;

                    $totalAmount += $ordering->parent_ticket_id !== null ? $ordering->getBackwardPrice() : $ordering->getPartnerPrice() ?? $ordering->getPrice();
                    $tickets[] = $ticket;
                    $result = [
                        'tickets' => $tickets,
                        'totalAmount' => $totalAmount
                    ];
                }
            }
        }
        return $result ?? [];
    }

}
