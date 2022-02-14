<?php

namespace App\Helper\Algebra;
class PercentageHelper
{
    public static function calcDecrease(int|float $startingValue, int|float $finalValue, int $precision = 2): float
    {
        return round((($startingValue - $finalValue) / $startingValue) * 100, $precision);
    }

    public static function calcIncrease(int|float $startingValue, int|float $finalValue, int $precision = 2): float
    {
        return round((($finalValue - $startingValue) / $finalValue) * 100, $precision);
    }
}