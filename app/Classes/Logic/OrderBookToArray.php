<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/22/2019
 * Time: 4:29 PM
 */

namespace App\Classes\Logic;
use function GuzzleHttp\Psr7\str;

class OrderBookToArray
{
    private static $bids;
    public static function parse($orderBook){
        foreach ($orderBook as $record){
            $key = trim($record['price'] . "a", "a");
            self::$bids[$key] = $record['qty'];
        }
        //dump(self::$bids);
        //die();
    }

    public static function update($orderBookDeltaUpdate){
        if ($orderBookDeltaUpdate['side'] == 'buy'){ // && self::$bids
            //dump($orderBookDeltaUpdate);
            //dump(self::$bids);
            // If volume = 0
            // Delete this key from the array
            if ($orderBookDeltaUpdate['qty'] == 0){
                unset(self::$bids[$orderBookDeltaUpdate['price']]);
            } else {
                // Else
                // Find a key
                // Update value
                self::$bids[$orderBookDeltaUpdate['price']] = $orderBookDeltaUpdate['qty'];
            }
        krsort(self::$bids);
        dump(self::$bids);
        }
    }
}