<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;

/**
 * Cache listener loop.
 * Output messages to console.
 *
 * Class test
 * @package App\Console\Commands
 */
class test extends Command
{
    private $arr = [];
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Cache::flush();
        while (true){
            $value = Cache::pull('consoleRead');

            if ($value){

                $derebitAsks = $value['derebit']['params']['data']['asks'];
                $cryptoFacAsks = array_reverse($value['cryptoFac']['asks']);
                $derebitBids = $value['derebit']['params']['data']['bids'];
                $cryptoFacBids = $value['cryptoFac']['bids'];

                $asksBooksData = [];
                for ($i = 0; $i <= 9; $i++){
                    array_push($asksBooksData, [$i, $derebitAsks[$i][0], $derebitAsks[$i][1], '', $cryptoFacAsks[$i][0], $cryptoFacAsks[$i][1]]);
                }

                $bidsBooksData = [];
                for ($i = 0; $i <= 9; $i++){
                    array_push($bidsBooksData, [$derebitBids[$i][1], $derebitBids[$i][0], '', $cryptoFacBids[$i][1], $cryptoFacBids[$i][0]]);
                }

                $headers = ['Bid', 'Price', 'Ask', 'Bid', 'Price', 'Ask'];
                $this->info('Derebit/CryptoFac books');
                $this->table($headers, array_merge($asksBooksData, $bidsBooksData));


                // $headers = ['CryptoFac: Price', 'Ask'];
                // $this->table($headers, $this->arr);
            }



            $headers = ['Price', 'Ask'];
            $data2 = [
                ['5060', '100'],
                ['5050', '10000'],
                ['5040', '500'],
                ['5030', '200']
            ];

        }

    }

}
