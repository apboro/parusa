<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\AccountTransactionStatus;
use App\Models\Dictionaries\ExcursionStatus;
use App\Models\Dictionaries\NewsStatus;
use App\Models\Dictionaries\OrderStatus;
use App\Models\Dictionaries\PartnerStatus;
use App\Models\Dictionaries\PaymentStatus;
use App\Models\Dictionaries\PiersStatus;
use App\Models\Dictionaries\PositionAccessStatus;
use App\Models\Dictionaries\PositionStatus;
use App\Models\Dictionaries\PromoCodeStatus;
use App\Models\Dictionaries\SeatStatus;
use App\Models\Dictionaries\ShipStatus;
use App\Models\Dictionaries\TerminalStatus;
use App\Models\Dictionaries\TicketStatus;
use App\Models\Dictionaries\TripDiscountStatus;
use App\Models\Dictionaries\TripSaleStatus;
use App\Models\Dictionaries\TripStatus;
use App\Models\Dictionaries\UserStatus;
use Database\Seeders\GenericSeeder;

class StatusesSeeder extends GenericSeeder
{
    protected array $data = [
        UserStatus::class => [
            UserStatus::active => ['name' => 'Действующий'],
            UserStatus::blocked => ['name' => 'Недействующий'],
        ],
        PartnerStatus::class => [
            PartnerStatus::active => ['name' => 'Действующий'],
            PartnerStatus::blocked => ['name' => 'Недействующий'],
        ],
        PositionStatus::class => [
            PositionStatus::active => ['name' => 'Действующий'],
            PositionStatus::blocked => ['name' => 'Недействующий'],
        ],
        PositionAccessStatus::class => [
            PositionAccessStatus::active => ['name' => 'Открыт', 'order' => 1],
            PositionAccessStatus::blocked => ['name' => 'Закрыт', 'order' => 2],
        ],

        ExcursionStatus::class => [
            ExcursionStatus::active => ['name' => 'Действующая'],
            ExcursionStatus::blocked => ['name' => 'Недействующая'],
        ],
        PiersStatus::class => [
            PiersStatus::active => ['name' => 'Действующий'],
            PiersStatus::blocked => ['name' => 'Недействующий'],
        ],
        ShipStatus::class => [
            ShipStatus::active => ['name' => 'Действующий'],
            ShipStatus::blocked => ['name' => 'Недействующий'],
        ],
        TripStatus::class => [
            TripStatus::regular => ['name' => 'По расписанию'],
            TripStatus::processing => ['name' => 'Выполняется'],
            TripStatus::finished => ['name' => 'Завершен'],
            TripStatus::cancelled => ['name' => 'Отменен'],
        ],
        TripSaleStatus::class => [
            TripSaleStatus::selling => ['name' => 'Идет продажа'],
            TripSaleStatus::closed_manually => ['name' => 'Продажа закрыта (вручную)'],
            TripSaleStatus::closed_automatically => ['name' => 'Продажа закрыта (автоматически)', 'enabled' => false],
        ],
        TripDiscountStatus::class => [
            TripDiscountStatus::enabled => ['name' => 'Разрешены'],
            TripDiscountStatus::disabled => ['name' => 'Запрещены'],
        ],

        AccountTransactionStatus::class => [
            AccountTransactionStatus::accepted => ['name' => 'Принято'],
        ],

        TicketStatus::class => [
            TicketStatus::partner_reserve => ['name' => 'Забронирован'],
            TicketStatus::partner_paid => ['name' => 'Оплачен'],
            TicketStatus::partner_returned => ['name' => 'Оформлен возврат'],
            TicketStatus::partner_reserve_canceled => ['name' => 'Отменён'],

            TicketStatus::promoter_wait_for_pay => ['name' => 'Промоутер: ожидает оплаты'],
            TicketStatus::promoter_confirmed => ['name' => 'Промоутер: оплата подтверждена'],
            TicketStatus::promoter_paid => ['name' => 'Промоутер: оплачен'],
            TicketStatus::promoter_canceled => ['name' => 'Промоутер: отменён'],

            TicketStatus::terminal_creating => ['name' => 'Оформление'],
            TicketStatus::terminal_creating_from_reserve => ['name' => 'Оформление'],
            TicketStatus::terminal_wait_for_pay => ['name' => 'Ожидает оплаты'],
            TicketStatus::terminal_wait_for_pay_from_reserve => ['name' => 'Ожидает оплаты'],
            TicketStatus::terminal_finishing => ['name' => 'Печать'],
            TicketStatus::terminal_paid => ['name' => 'Оплачен'],
            TicketStatus::terminal_wait_for_return => ['name' => 'Ожидает возврата'],
            TicketStatus::terminal_returned => ['name' => 'Оформлен возврат'],
            TicketStatus::terminal_canceled => ['name' => 'Отменён'],

            TicketStatus::showcase_creating => ['name' => 'Оформление'],
            TicketStatus::showcase_wait_for_pay => ['name' => 'Ожидает оплаты'],
            TicketStatus::showcase_paid => ['name' => 'Оплачен'],
            TicketStatus::showcase_returned => ['name' => 'Оформлен возврат'],
            TicketStatus::showcase_canceled => ['name' => 'Отменён'],

            TicketStatus::used => ['name' => 'Использован'],
            TicketStatus::expired => ['name' => 'Просрочен'],

            TicketStatus::showcase_paid_single => ['name' => 'ЕдБ оплачен витрина'],
            TicketStatus::partner_paid_single => ['name' => 'ЕдБ оплачен партнер'],
            TicketStatus::terminal_paid_single => ['name' => 'ЕдБ оплачен терминал'],
            TicketStatus::promoter_paid_single => ['name' => 'Промоутер ЕдБ оплачен'],
            TicketStatus::api_confirmed_single => ['name' => 'API ЕдБ подтвержден'],
            TicketStatus::yaga_confirmed_single => ['name' => 'Афиша ЕдБ подтвержден'],

            TicketStatus::api_reserved => ['name' => 'Зарезервирован по Api'],
            TicketStatus::api_confirmed => ['name' => 'Подтвержден по Api'],
            TicketStatus::api_canceled => ['name' => 'Отменён'],
            TicketStatus::api_returned => ['name' => 'Возвращён по Api'],
            TicketStatus::yaga_reserved => ['name' => 'Резерв Афиша'],
            TicketStatus::yaga_confirmed => ['name' => 'Подтвержден Афиша'],
            TicketStatus::yaga_canceled => ['name' => 'Отменен Афиша'],
            TicketStatus::yaga_canceled_with_penalty => ['name' => 'Отменен со штрафом Афиша '],
            // использован после просрочки
        ],

        OrderStatus::class => [
            OrderStatus::partner_reserve => ['name' => 'Бронь'],
            OrderStatus::partner_paid => ['name' => 'Оплачен'],
            OrderStatus::partner_returned => ['name' => 'Оформлен возврат'],
            OrderStatus::partner_partial_returned => ['name' => 'Оформлен частичный возврат'],
            OrderStatus::partner_reserve_canceled => ['name' => 'Отменён'],
            OrderStatus::promoter_wait_for_pay => ['name' => 'Промоутер: ожидает оплаты'],
            OrderStatus::promoter_confirmed => ['name' => 'Промоутер: оплата подтверждена'],
            OrderStatus::promoter_paid => ['name' => 'Промоутер: оплачен'],
            OrderStatus::promoter_canceled => ['name' => 'Промоутер: отменен'],
            OrderStatus::terminal_creating => ['name' => 'Новый'],
            OrderStatus::terminal_creating_from_reserve => ['name' => 'Новый'],
            OrderStatus::terminal_wait_for_pay => ['name' => 'Ожидает оплаты'],
            OrderStatus::terminal_wait_for_pay_from_reserve => ['name' => 'Ожидает оплаты'],
            OrderStatus::terminal_finishing => ['name' => 'Завершение оформления'],
            OrderStatus::terminal_paid => ['name' => 'Оплачен'],
            OrderStatus::terminal_canceled => ['name' => 'Отменён'],
            OrderStatus::terminal_returned => ['name' => 'Оформлен возврат'],
            OrderStatus::terminal_partial_returned => ['name' => 'Оформлен частичный возврат'],
            OrderStatus::terminal_wait_for_return => ['name' => 'Ожидает возврата'],
            OrderStatus::showcase_creating => ['name' => 'Оформление'],
            OrderStatus::showcase_wait_for_pay => ['name' => 'Ожидает оплаты'],
            OrderStatus::showcase_confirmed => ['name' => 'Оплата подтверждена'],
            OrderStatus::showcase_paid => ['name' => 'Оплачен'],
            OrderStatus::showcase_returned => ['name' => 'Оформлен возврат'],
            OrderStatus::showcase_partial_returned => ['name' => 'Оформлен частичный возврат'],
            OrderStatus::showcase_canceled => ['name' => 'Отменён'],
            OrderStatus::done => ['name' => 'Выполнен'],
            OrderStatus::api_reserved => ['name' => 'Зарезервирован по Api'],
            OrderStatus::api_confirmed => ['name' => 'Подтвержден по Api'],
            OrderStatus::api_canceled => ['name' => 'Отменён'],
            OrderStatus::api_returned => ['name' => 'Возвращён по Api'],
            OrderStatus::yaga_reserved => ['name' => 'Зарезервирован афиша'],
            OrderStatus::yaga_confirmed => ['name' => 'Подтвержден афиша'],
            OrderStatus::yaga_canceled => ['name' => 'Отменён афиша'],
            OrderStatus::yaga_canceled_with_penalty => ['name' => 'Отменён со штрафом Афиша'],
        ],

        TerminalStatus::class => [
            TripDiscountStatus::enabled => ['name' => 'Работает'],
            TripDiscountStatus::disabled => ['name' => 'Не работает'],
        ],

        PaymentStatus::class => [
            PaymentStatus::sale => ['name' => 'Оплата'],
            PaymentStatus::return => ['name' => 'Возврат'],
        ],
        PromoCodeStatus::class => [
            PromoCodeStatus::active => ['name' => 'Активный'],
            PromoCodeStatus::blocked => ['name' => 'Неактивный'],
        ],
        SeatStatus::class => [
            SeatStatus::vacant => ['name' => 'Свободно'],
            SeatStatus::reserve => ['name' => 'Зарезервировано'],
            SeatStatus::occupied => ['name' => 'Занято'],
        ],
        NewsStatus::class => [
            NewsStatus::DRAFT => ['name' => 'Черновик'],
            NewsStatus::SENT => ['name' => 'Отправлена'],
        ]
    ];
}
