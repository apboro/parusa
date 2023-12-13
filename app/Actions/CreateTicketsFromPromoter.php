<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongTicketCreatorException;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Ships\Seats\TripSeat;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;

class CreateTicketsFromPromoter
{
    public function __construct(private readonly Currents $current)
    {
    }

    public function execute(array $data): array
    {
        $totalAmount = 0;
        foreach ($data['tickets'] as $id => $ticketInfo) {
            if ($ticketInfo['quantity'] > 0) {
                $ordering = PositionOrderingTicket::query()
                    ->where(['id' => $id, 'position_id' => $this->current->positionId(), 'terminal_id' => null])
                    ->first();
                if (!$ordering)
                    throw new WrongTicketCreatorException();

                if ($ordering->seat) {
                    TripSeat::query()
                        ->updateOrCreate(['trip_id' => $ordering->trip->id, 'seat_id' => $ordering->seat->id],
                            ['status_id' => SeatStatus::occupied]);
                }

                for ($i = 1; $i <= $ticketInfo['quantity']; $i++) {
                    /** @var PositionOrderingTicket $ordering */
                    $ticket = new Ticket([
                        'trip_id' => $ordering->trip_id,
                        'grade_id' => $ordering->grade_id,
                        'base_price' => $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : $ticketInfo['price'],
                        'status_id' => TicketStatus::promoter_wait_for_pay,
                        'provider_id' => $ordering->trip->provider_id,
                        'seat_id' => $ordering->seat_id,
                    ]);

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
