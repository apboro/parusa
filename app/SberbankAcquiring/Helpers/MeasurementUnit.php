<?php

namespace App\SberbankAcquiring\Helpers;

/**
 * Helper class with measurement codes.
 */
class MeasurementUnit
{
    public const pcs = 0; // Применяется для предметов расчета, которые могут быть реализованы поштучно или единицами (а также в случае, если предметом расчета является товар, подлежащий обязательной маркировке средством идентификации (передан mark_code))
    public const gram = 10; // Грамм
    public const kg = 11; // Килограмм
    public const tonn = 12; // Тонна
    public const cm = 20; // Сантиметр
    public const dm = 21; // Дециметр
    public const m = 22; // Метр
    public const cm_2 = 30; // Квадратный сантиметр
    public const dm_2 = 31; // Квадратный дециметр
    public const m_2 = 32; // Квадратный метр
    public const mm = 40; // Миллилитр
    public const liter = 41; // Литр
    public const m_3 = 42; // Кубический метр
    public const kwt_h = 50; // Киловатт час
    public const giga_kal = 51; // Гигакалория
    public const day = 70; // Сутки (день)
    public const hour = 71; // Час
    public const minute = 72; // Минута
    public const second = 73; // Секунда
    public const kilobyte = 80; // Килобайт
    public const megabyte = 81; // Мегабайт
    public const gigabyte = 82; // Гигабайт
    public const terabyte = 83; // Терабайт
    public const another = 255; // Применяется при использовании иных мер измерения
}
