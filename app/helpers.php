<?php

// date format

use App\Models\Currency;
use NumberToWords\NumberToWords;

function df($date, $format = 'd.m.Y')
{
    return date($format, strtotime($date));
}

// number format
function nf($number, $decimals = 0)
{
    return number_format($number, $decimals, '.', ',');
}


// numberToWords
function numberToWords($lang, $number)
{
    $word = NumberToWords::transformNumber($lang, $number);
    return $word;
}


// currency_rate
function currency_rate()
{
    $currency = Currency::first();
    return $currency->rate;
}


// render_price_with_symbol_placement
function render_price_with_symbol_placement($price)
{
    return $price . ' uzs';
}
