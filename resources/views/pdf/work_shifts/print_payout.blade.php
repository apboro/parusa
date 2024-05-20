<!DOCTYPE html>
<?php

use App\Models\WorkShift\WorkShift;
use App\Settings;

/** @var WorkShift $workShift */
?>

<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" style="margin: 0; padding: 0; width: 226pt;">
<head>
    <meta charset="utf-8">
    <title>Расписка N{{ $workShift->id }}</title>
    <style>
        @page {
            size: 226pt 340pt;
            margin: 0;
            padding: 0;
        }



        /** {*/
        /*    outline: 1px solid rgba(255, 0, 242, 0.49);*/
        /*}*/
    </style>
</head>

<body style="font-family:'Reforma Grotesk',serif; box-sizing: border-box; margin: 2%; padding: 0.5pt; width: 226pt;">
<div>
    <div>ФИО: {{$workShift->promoter->name}}</div>
    <div>Ставка: {{$workShift->tariff->commission}} %</div>
    <div>Ставка партнёры: {{Settings::get('promoters_commission_integrated_excursions', null, Settings::int)}} %</div>
    <div>Начало смены: {{\Carbon\Carbon::parse($workShift->start_at)->translatedFormat('D, d M Y H:i')}}</div>
    @if ($workShift->pay_for_time != 0)
        <div>Почасовая оплата: {{$workShift->pay_for_time}} руб.</div>
    @endif
    @if ($workShift->pay_for_out != 0)
        <div>Оплата за выход: {{$workShift->pay_for_out}} руб.</div>
    @endif
    @if ($workShift->pay_commission != 0)
        <div>Комиссионные: {{$workShift->pay_commission}} руб.</div>
    @endif

    <div>Сумма получена: {{$workShift->paid_out}} руб.</div>
    @if ($workShift->balance != 0)
        <div>Остаток на балансе: {{$workShift->balance}} руб.</div>
    @endif
    <span>Подпись:</span><p style="border-bottom: 1px solid #000; width: 125px; margin-left: 47px"></p>
</div>
</body>
</html>
