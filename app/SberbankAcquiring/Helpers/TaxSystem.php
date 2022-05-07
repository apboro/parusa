<?php

namespace App\SberbankAcquiring\Helpers;

/**
 * Helper class with tax systems codes.
 */
class TaxSystem
{
    public const general = 0;
    public const simplified_income = 1;
    public const simplified_income_minus_expences = 2;
    public const unified_tax_on_imputed_income = 3;
    public const unified_agricultural_tax = 4;
    public const patent_taxation_system = 5;
}
