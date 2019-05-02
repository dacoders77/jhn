<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/22/2019
 * Time: 4:29 PM
 */

namespace App\Classes\Logic;

/**
 * Parse Cryptofac order book.
 * Each event does not contain a fool book, it contains updates only.
 * We need to make arrays ourselves.
 *
 * Class OrderBookToArray
 * @package App\Classes\Logic
 */
class OrderBookToArray
{
    public static $bids;
    public static $asks;

    public static function parse($orderBook){
        foreach ($orderBook['bids'] as $record){
            $key = trim($record['price'] . "a", "a"); // Added 'a' and removed it. Needed for type conversion. May be not needed anymore
            self::$bids[$key] = $record['qty'];
        }
        foreach ($orderBook['asks'] as $record){
            $key = trim($record['price'] . "a", "a");
            self::$asks[$key] = $record['qty'];
        }
    }

    public static function update($orderBookDeltaUpdate){
        if ($orderBookDeltaUpdate['side'] == 'buy'){
            // If volume = 0, remove this key from the array
            if ($orderBookDeltaUpdate['qty'] == 0){
                unset(self::$bids[$orderBookDeltaUpdate['price']]);
            } else {
                self::$bids[$orderBookDeltaUpdate['price']] = $orderBookDeltaUpdate['qty']; // Find the key, update the value
            }
        krsort(self::$bids);
        }

        if ($orderBookDeltaUpdate['side'] == 'sell'){ // && self::$bids
            if ($orderBookDeltaUpdate['qty'] == 0){
                unset(self::$asks[$orderBookDeltaUpdate['price']]);
            } else {
                self::$asks[$orderBookDeltaUpdate['price']] = $orderBookDeltaUpdate['qty'];
            }
        krsort(self::$asks);
        }

        return([
            'bids' => self::$bids,
            'asks' => self::$asks
        ]);
    }

    private function updateBook(){
        //
    }
}