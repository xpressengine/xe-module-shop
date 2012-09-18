<?php

class ShopDisplay
{
    public static function priceFormat($price, $currency)
    {
        return self::numberFormat($price) . ' ' . $currency;
    }

    public static function numberFormat($number)
    {
        return number_format($number, 2);
    }
}