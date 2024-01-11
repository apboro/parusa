<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\OrderType;
use Database\Seeders\GenericSeeder;

class OrderTypesSeeder extends GenericSeeder
{
    /**
     * @var array|\array[][]
     *
     * Defaults:
     * 'enabled' => true
     */
    protected array $data = [
        OrderType::class => [
            OrderType::partner_sale => ['name' => 'Личный кабинет партнёра'],
            OrderType::partner_site => ['name' => 'Витрина на сайте партнера'],
            OrderType::site => ['name' => 'Витрина на сайте компании «Алые паруса»'],
            OrderType::terminal => ['name' => 'Мобильная касса'],
            OrderType::terminal_partner => ['name' => 'Мобильная касса (промоутер)'],
            OrderType::qr_code => ['name' => 'QR-код'],
            OrderType::referral_link => ['name' => 'Реферальная ссылка'],
            OrderType::promoter_sale => ['name' => 'Личный кабинет промоутера'],
            OrderType::api_sale => ['name' => 'Продажа по Api'],
        ],
    ];
}
