<?php

namespace App\Actions;

use App\Exceptions\Tickets\WrongOrderStatusException;
use App\Exceptions\Tickets\WrongTicketCreatorException;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Positions\PositionOrderingTicket;
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
                for ($i = 1; $i <= $ticketInfo['quantity']; $i++) {
                    /** @var PositionOrderingTicket $ordering */
                    $ticket = new Ticket([
                        'trip_id' => $ordering->trip_id,
                        'grade_id' => $ordering->grade_id,
                        'status_id' => $ticketStatus,
                        'base_price' => $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : $ticketInfo['price'],
                        'provider_id' => $ordering->trip->provider_id,
                        'seat_number' => $ordering->seat_number,
                    ]);

                    $ticket->cart_ticket_id = $ordering->id;
                    $ticket->cart_parent_ticket_id = $ordering->parent_ticket_id;
                    $ticket->backward_price = $ordering->parent_ticket_id ? $ordering->getBackwardPrice() : null;

                    $tickets[] = $ticket;
                }
            }
        }
        return $tickets ?? [];
    }

}
