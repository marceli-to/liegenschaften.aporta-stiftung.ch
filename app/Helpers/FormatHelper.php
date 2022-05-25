<?php
namespace App\Helpers;

class MoneyFormatHelper
{
  public static function round($amount)
  { 
    // change format to decimal with precision of 2
    $result = number_format($amount, 2, '.', '');

    // get last digital
    $lastDigit = substr($result,-1,1);

    // round up to next 1/10
    if ($lastDigit > 5)
    {
      $result = round($result,1);
    }

    if ($lastDigit > 0 && $lastDigit < 5)
    {
      $result = round($result,1) + 0.05;
    }

    return (float) number_format($result, 2, '.', '\'');
  }

  public static function number($amount)
  {
    return number_format(round($amount * 20) / 20, 2, '.', '');
  }
}