<?php

return [
    'ticket_template' => env('PDF_TICKET_A4', 'pdf/ticket_a4_v3'),
    'ticket_template_neva' => 'pdf/ticket_a4_neva',
    'ticket_print' => env('PDF_TICKET_PRINT', 'pdf/ticket_print'),
    'ticket_template_single' => 'pdf/ticket_a4_v3_single',
    'order_template' => env('PDF_ORDER_A4', 'pdf/order_a4'),
    'order_template_single' => 'pdf/order_a4_v3_single',
    'order_print' => env('PDF_ORDER_PRINT', 'pdf/order_print'),
    'order_print_single' => 'pdf/order_print_v2_single',
    ];
