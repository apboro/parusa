<?php

namespace App\SberbankAcquiring\Helpers;

class PaymentObject
{
    public const goods = 1; // товар;
    public const excised_goods = 2; // подакцизный товар;
    public const job = 3; // работа;
    public const service = 4; // услуга;
    public const stake_in_gambling = 5; // ставка азартной игры;
    public const gambling_gain = 6; // выигрыш азартной игры;
    public const lottery_ticket = 7; // лотерейный билет;
    public const lottery_gain = 8; // выигрыш лотереи;
    public const intellectual_property_provision = 9; // предоставление РИД;
    public const payment = 10; // платёж;
    public const agent_commission = 11; // агентское вознаграждение;
    public const combined = 12; // составной предмет расчёта;
    public const other = 13; // иной предмет расчёта;
    // public const a = 14; // имущественное право;
    // public const a = 15; // внереализационный доход;
    // public const a = 16; // страховые взносы: о суммах расходов, уменьшающих сумму налога (авансовых платежей) в соответствии с пунктом 3.1 статьи 346.21 Налогового кодекса Российской Федерации;
    // public const a = 17; // торговый сбор: о суммах уплаченного торгового сбора;
    // public const a = 18; // курортный сбор.
}
