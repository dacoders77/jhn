<?php

namespace App\Console\Commands;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Helper\TableStill;

/**
 * Cache listener loop.
 * Output messages to console.
 *
 * Class test
 * @package App\Console\Commands
 */
class test extends Command
{
    private $isFirstRun = true;
    private $timeToCompare = null;

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

        $this->output->write(sprintf("\033\143")); // Clear screen
        //$this->tabeStill = new \App\Classes\ConsoleGraphics\TableStill($this->output);

        $bar = $this->output->createProgressBook(500, $this);

        while (true){
            $value = Cache::pull('consoleRead');

            //$time = date("Y-m-d G:i:s", time());
            $time = microtime(true);
            //dump($time);
            //dump(strtotime($time));
            //dd(strtotime($time . '1second'));

            if ($value){

                if ($time > $this->timeToCompare || $this->isFirstRun){

                    $this->timeToCompare = $time + 0.1; // + sec
                    $this->isFirstRun = false;

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
                    $rows = array_merge($asksBooksData, $bidsBooksData);
                    // $this->table($headers, $rows); // Headers, table daata. Works good

                    $tableStill = new \App\Classes\ConsoleGraphics\TableStill($this->output);
                    if ($rows instanceof Arrayable) {
                        $rows = $rows->toArray();
                    }

                    $tableStill->setHeaders((array) $headers)->setRows($rows)->setStyle('default');

                    foreach ([] as $columnIndex => $columnStyle) {
                        $tableStill->setColumnStyle($columnIndex, $columnStyle);
                    }
                    echo "\n"; // Added because the first line was flickering.
                    $tableStill->render();



                    $bar->advance(1, $headers, array_merge($asksBooksData, $bidsBooksData));
                    usleep(1);
                }
            }
        }
    }
}
