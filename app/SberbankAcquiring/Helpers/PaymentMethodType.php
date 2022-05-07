<?php

namespace App\SberbankAcquiring\Helpers;

class PaymentMethodType
{
    public const full_prepayment = 1; // полная предварительная оплата до момента передачи предмета расчёта;
    public const partial_prepayment = 2; // частичная предварительная оплата до момента передачи предмета расчёта;
    public const advance_payment = 3; // аванс;
    public const full_payment = 4; // полная оплата в момент передачи предмета расчёта;
    public const partial_payment_with_further_credit = 5; // частичная оплата предмета расчёта в момент его передачи с последующей оплатой в кредит;
    public const no_payment_with_further_credit = 6; // передача предмета расчёта без его оплаты в момент его передачи с последующей оплатой в кредит;
    public const payment_on_credit = 7; // оплата предмета расчёта после его передачи с оплатой в кредит.
}
