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

                $derebitData = $value['derebit']['params']['data']['bids'];
                dump($value['cryptoFac']['asks']);
                //dump($derebitData);

                $this->arr = [];
                foreach ($value['cryptoFac']['asks'] as $key => $value){
                    //dump($record);
                    array_push($this->arr, [$key, $value]);
                }

                $arr2 = [];
                for ($i = 0; $i <= 9; $i++){
                    array_push($arr2, [$derebitData[$i][0], $derebitData[$i][1], 3, 4]);
                }


                $headers = ['Derebit: Price', 'Ask', 'CryptoFac: Price', 'Ask'];
                //$this->table($headers, $derebitData);
                $this->table($headers, $arr2);

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
