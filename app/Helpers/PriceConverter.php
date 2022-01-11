<?php

namespace App\Helpers;

class PriceConverter
{
    /**
     * Convert price to store value.
     *
     * @param float $value
     *
     * @return  int
     */
    public static function priceToStore(float $value): int
    {
        return floor($value * 100);
    }

    /**
     * Convert store value to price.
     *
     * @param int $value
     *
     * @return  float
     */
    public static function storeToPrice(int $value): float
    {
        return $value / 100;
    }
}
