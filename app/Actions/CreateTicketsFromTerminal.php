<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongOrderStatusException;
use App\Exceptions\Tickets\WrongTicketCreatorException;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Positions\PositionOrderingTicket;
use App\Models\Ships\Seats\TripSeat;
use App\Models\Tickets\Ticket;
use App\Models\User\Helpers\Currents;

class CreateTicketsFromTerminal
{
    public function __construct(private readonly Currents $current)
    {

    }
    public function execute(array $data, ?int $orderStatus): array
    {
        foreach ($data['tickets'] as $id => $ticketInfo) {
            if ($ticketInfo['quantity'] > 0) {
                if (null === ($ordering = PositionOrderingTicket::query()
                        ->where(['id' => $id, 'position_id' => $this->current->positionId(), 'terminal_id' => $this->current->terminalId()])
                        ->first())
                ) {
                    throw new WrongTicketCreatorException();
                }
                $ticketStatus = match ($orderStatus) {
                    OrderStatus::terminal_creating => TicketStatus::terminal_creating,
                    default => throw new WrongOrderStatusException(),
                };

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
                        'status_id' => $ticketStatus,
                        'base_price' => $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : $ticketInfo['price'],
                        'provider_id' => $ordering->trip->provider_id,
                        'seat_id' => $ordering->seat_id,
                        'start_pier_id' => $ordering->start_pier_id ?? null,
                    ]);

                    $ticket->cart_ticket_id = $ordering->id;
                    $ticket->cart_parent_ticket_id = $ordering->parent_ticket_id;
                    $ticket->backward_price = $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : null;
                    $ticket->menu_id = $ordering->menu_id ?? null;

                    $tickets[] = $ticket;
                }
            }
        }
        return $tickets ?? [];
    }

}
