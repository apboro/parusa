<?php

namespace Database\Seeders\Dictionaries;

use App\Models\Dictionaries\AccountTransactionType;
use Database\Seeders\GenericSeeder;

class AccountTransactionTypesSeeder extends GenericSeeder
{
    /**
     * @var array|string[][][]
     *
     * Defaults:
     * 'enabled' => true
     * 'lock' => false
     */
    protected array $data = [
        AccountTransactionType::class => [
            AccountTransactionType::account_refill => [
                'name' => 'Пополнение счета',
                'sign' => 0,
                'final' => false,
                'next_title' => 'Способ пополнения',
            ],
            AccountTransactionType::account_refill_invoice => [
                'name' => 'По счету',
                'sign' => 1,
                'parent_type_id' => AccountTransactionType::account_refill,
                'final' => true,
                'has_reason' => true,
                'reason_title' => 'Номер счёта',
                'has_reason_date' => true,
                'reason_date' => 'Дата счёта',
            ],
            AccountTransactionType::account_refill_cash => [
                'name' => 'Наличными',
                'sign' => 1,
                'parent_type_id' => AccountTransactionType::account_refill,
                'final' => true,
                'has_reason' => false,
                'has_reason_date' => false,
            ],
            AccountTransactionType::tickets_buy => [
                'name' => 'Покупка билетов',
                'sign' => -1,
                'final' => true,
            ],
            AccountTransactionType::tickets_buy_return => [
                'name' => 'Возврат билетов',
                'sign' => 1,
                'final' => true,
            ],
            AccountTransactionType::tickets_sell_commission => [
                'name' => 'Начисление комиссионных',
                'sign' => 1,
                'final' => true,
            ],
            AccountTransactionType::tickets_sell_commission_return => [
                'name' => 'Возврат комиссионных',
                'sign' => -1,
                'final' => true,
            ],
        ],
    ];
}
