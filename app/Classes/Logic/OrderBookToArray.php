<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/22/2019
 * Time: 4:29 PM
 */

namespace App\Classes\Logic;
use App\Classes\LogToFile;

/**
 * Parse Cryptofac order book.
 * Each event does not contain a fool book, it contains updates only.
 * We need to make arrays ourselves.
 *
 * Class OrderBookToArray
 * @package App\Classes\Logic
 * @return array
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
        if (self::$bids) krsort(self::$bids);
        }

        if ($orderBookDeltaUpdate['side'] == 'sell'){ // && self::$bids
            if ($orderBookDeltaUpdate['qty'] == 0){
                unset(self::$asks[$orderBookDeltaUpdate['price']]);
            } else {
                self::$asks[$orderBookDeltaUpdate['price']] = $orderBookDeltaUpdate['qty'];
            }
        if (self::$asks) krsort(self::$asks);
        }

        $asks = [];
        //LogToFile::add(__FILE__ . ' asks: ', json_encode(self::$bids));
        if (self::$asks)
        foreach (self::$asks as $key => $value){
            array_push($asks, [(float)$key, $value]);
        }
        $bids = [];
        if (self::$bids)
        //LogToFile::add(__FILE__ . ' bids: ', json_encode(self::$bids));
        foreach (self::$bids as $key => $value){
            array_push($bids, [(float)$key, $value]);
        }

        return([
            'asks' => $asks,
            'bids' => $bids
        ]);
    }
}