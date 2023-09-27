<?php

return [
    'ticket_template' => env('PDF_TICKET_A4', 'pdf/ticket/ticket_a4_v3'),
    'ticket_template_neva' => 'pdf/ticket/ticket_a4_neva',
    'ticket_template_city_tour' => 'pdf/ticket/ticket_a4_city_tour',
    'ticket_template_single' => 'pdf/ticket/ticket_a4_edin',

    'order_template' => env('PDF_ORDER_A4', 'pdf/order/order_a4'),
    'order_template_single' => 'pdf/order/order_a4_edin',
    'order_template_city_tour' => 'pdf/order/order_a4_city_tour',
    'order_template_neva' => 'pdf/order/order_a4_neva',

    'ticket_print' => env('PDF_TICKET_PRINT', 'pdf/talon/ticket_print'),
    'order_print' => env('PDF_ORDER_PRINT', 'pdf/talon/order_print'),
    'order_print_single' => 'pdf/talon/order_print_v2_edin',
    'order_print_city_tour' => 'pdf/talon/order_print_city_tour',
    ];
