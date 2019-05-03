<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Helper\Table;

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
        $bar = $this->output->createProgressBook(500, $this);
        $bar = $this->output->createProgressBook2();
        $i = 0;
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
                //$this->table($headers, array_merge($asksBooksData, $bidsBooksData)); // Headers, table daata

                    // ... do some work

                    // advances the progress bar 1 unit
                    $bar->advance(1, $headers, array_merge($asksBooksData, $bidsBooksData));
                    usleep(1);
                    // you can also advance the progress bar by more than 1 unit
                    // $progressBar->advance(3);


            }

        }

    }

}
