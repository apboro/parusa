<?php

namespace App\Services\YagaAPI\Model;

use App\Models\Order\Order;

class YagaTicket
{

    public function __construct(private readonly Order $order)
    {

    }

    public function getResource(): array
    {
        foreach ($this->order->tickets as $ticket) {
            $tickets[] =
                [
                    "admission" => true,
                    "barcode" => $ticket->qrData(),
                    "barcodeType" => 'QR_CODE',
                    "categoryId" => $ticket->grade_id,
                    "categoryName" => $ticket->grade->name,
                    "id" => $ticket->id,
                    "levelId" => $ticket->grade->id,
                    "levelName" => $ticket->grade->name,
                    "organizerInfo" => 'Алые Паруса',
                    "pdfUrl" => $ticket->qr(),
                ];
        }

        return $tickets ?? [];
    }
}
