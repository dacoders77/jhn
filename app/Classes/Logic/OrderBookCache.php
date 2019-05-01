<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/22/2019
 * Time: 2:17 PM
 */
namespace App\Classes\Logic;
use Illuminate\Support\Facades\Cache;

class OrderBookCache
{
    public static $derebitOrderBook;
    public static $cryptofacOrderBook;

    public static function check(){
        if (self::$derebitOrderBook && self::$cryptofacOrderBook){
            // Calculate WVAP
        }
    }
}