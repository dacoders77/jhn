<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/26/2019
 * Time: 11:22 PM
 */

namespace App\Classes\Logic;
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

    public function __construct($console)
    {
        $this->console = $console;
    }

    public function check($leg){

        // Access cache
        // Get Cryptofac value

        // if (derebit && Cryptofas) => hohoh
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
        }
    }
}