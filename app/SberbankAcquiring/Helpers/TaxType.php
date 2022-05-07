<?php

namespace App\SberbankAcquiring\Helpers;

/**
 * Helper class with tax types codes.
 */
class TaxType
{
    public const no_vat = 0;
    public const vat_0 = 1;
    public const vat_10 = 2;
    public const vat_18 = 3;
    public const vat_20 = 6;
    public const vat_applicable_rate_10_110 = 4;
    public const vat_applicable_rate_18_118 = 5;
    public const vat_applicable_rate_20_120 = 7;
}
