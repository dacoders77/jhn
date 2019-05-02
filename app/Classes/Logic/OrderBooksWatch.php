<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/26/2019
 * Time: 11:22 PM
 */

namespace App\Classes\Logic;
use App\Console\Commands\cryptofac;
use App\Console\Commands\derebit;
use Illuminate\Support\Facades\Cache;

/**
 * Called from Derebit and Cryptofac WS listeners
 *
 * Class OrderBooksWatch
 * @package App\Classes\Logic
 */
class OrderBooksWatch
{
    private $console;
    private $books;

    public function __construct($console)
    {
        $this->console = $console;
    }

    public function check($leg){

        if (Cache::get('isDerebitOrderBookReceived') && Cache::get('isCryptoFacOrderBookReceived')){

            // Get both books from cache
            $this->books = [
                'derebit' => Cache::get('derebitBook'),
                'cryptoFac' => Cache::get('cryptoFacBook')
            ];


            Cache::put('consoleRead', $this->books, now()->addMinute(1));
        }
        else {
            Cache::put('consoleRead', 'Not both books. Current book: ' . $leg, now()->addMinute(1));
        }



        /*// if (derebit && Cryptofas) => execute
        if (Cache::get('cryptoFac')){

            //$this->console->error('Both order books are ready');
            Cache::put('consoleRead', 'gggg=g=g=g ' . $leg, now()->addMinute(1)); // Output

            // WVAP

            // if (leg 1 vol > leg 2 vol){
            //  !correction = 0;
            //} else [
            //  calculate $correction
            //}
        }
        else{
            dump(__FILE__ . ' Waiting for CryptoFac ');
        }*/
    }
}