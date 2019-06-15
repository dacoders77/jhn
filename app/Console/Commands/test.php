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
    private $asksBooksData;
    private $isFirstRun = true;
    private $timeToCompare = null;
    private $size;
    private $hedgeSize;
    private $accumSizeVolume;
    private $limitOrderCorrection;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test {volume}';

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
        //$bar = $this->output->createProgressBook(500, $this);

        while (true){
            $value = Cache::pull('consoleRead');
            $time = microtime(true);

            if ($value){
                if (true){

                    $this->timeToCompare = $time + 0.1; // + sec
                    $this->isFirstRun = false;

                    $derebitAsks = array_reverse($value['derebit']['params']['data']['asks']);
                    $cryptoFacAsks = array_reverse($value['cryptoFac']['asks']);

                    $derebitBids = $value['derebit']['params']['data']['bids'];
                    $cryptoFacBids = $value['cryptoFac']['bids'];

                    $this->size = $this->argument('volume');
                    $this->asksBooksData = [];
                    for ($i = 0; $i <= 9; $i++){

                        //                                     Bid  |   Derebit Price         | Ask |           * |Bid|   CryptofacPrice         | Ask |                 * | info
                        if ($i == 0) array_push($this->asksBooksData, [$i, $derebitAsks[$i][0], $derebitAsks[$i][1], '', '', $cryptoFacAsks[$i][0], $cryptoFacAsks[$i][1], '', 'Spreads to buy: ' . $this->size ]);
                        if ($i == 1) array_push($this->asksBooksData, [$i, $derebitAsks[$i][0], $derebitAsks[$i][1], '', '', $cryptoFacAsks[$i][0], $cryptoFacAsks[$i][1], '', 'leg1 > size: ' . ($derebitAsks[9][1] > $this->size ? 'true' : 'false')]);
                        if ($i == 2) array_push($this->asksBooksData, [$i, $derebitAsks[$i][0], $derebitAsks[$i][1], '', '', $cryptoFacAsks[$i][0], $cryptoFacAsks[$i][1], '', 'Cur  sprd val: ' . ($cryptoFacAsks[9][0] - $derebitAsks[9][0])]);
                        // WVAP spread
                        if ($i == 3) array_push($this->asksBooksData, [$i, $derebitAsks[$i][0], $derebitAsks[$i][1], '', '', $cryptoFacAsks[$i][0], $cryptoFacAsks[$i][1], '', 'WVAP sprd val: ' . ($cryptoFacAsks[9][0] - $this->accumSizeVolume)]);
                        // Correction
                        $this->limitOrderCorrection = round((($cryptoFacAsks[9][0] - $derebitAsks[9][0]) - ($cryptoFacAsks[9][0] - $this->accumSizeVolume)) * 2) / 2;
                        if ($i == 4) array_push($this->asksBooksData, [$i, $derebitAsks[$i][0], $derebitAsks[$i][1], '', '', $cryptoFacAsks[$i][0], $cryptoFacAsks[$i][1], '', 'Correction   : ' . $this->limitOrderCorrection ]);
                        if ($i == 5 || $i == 6 || $i == 7 || $i == 8 || $i == 9) array_push($this->asksBooksData, [$i, $derebitAsks[$i][0], $derebitAsks[$i][1], '', '', $cryptoFacAsks[$i][0], $cryptoFacAsks[$i][1], '']);

                    }

                    $bidsBooksData = [];
                    for ($i = 0; $i <= 9; $i++){
                        array_push($bidsBooksData, [$derebitBids[$i][1], $derebitBids[$i][0], '', '', $cryptoFacBids[$i][1], $cryptoFacBids[$i][0]]);
                    }

                    if($derebitAsks[9][1] > $this->size) {
                        //$this->output->write(sprintf("\033\143")); // Clear screen
                    }

                    $this->derebitAsteriskFill();
                    $this->cryptoFacAsteriskFill($this->limitOrderCorrection);

                    $headers = ["Bid. L1-D", 'Price     ', 'Ask     ', '*     ', 'Bid L2-C', 'Price     ', 'Ask     ', '*     ', 'info                                 '];
                    $rows = array_merge($this->asksBooksData, $bidsBooksData);

                    $tableStill = new \App\Classes\ConsoleGraphics\TableStill($this->output);
                    $tableStill->setHeaders((array) $headers)->setRows($rows)->setStyle('default');

                    echo "\n"; // Added because the first line was flickering.
                    $tableStill->render();

                    usleep(1);
                }
            }
        }
    }

    private function derebitAsteriskFill(){
        $accumulatedVolume = 0;
        $this->hedgeSize = 0;
        for($i = 9; $i >= 0; $i--) {

            $this->hedgeSize = $this->hedgeSize + $this->asksBooksData[$i][2];// $this->size - ($this->hedgeSize - $this->asksBooksData[$i][2]);

            if ($this->hedgeSize <= $this->size) {
                $accumulatedVolume = $accumulatedVolume + $this->asksBooksData[$i][2];
                $this->asksBooksData[$i][3] = $this->asksBooksData[$i][2];
                $this->accumSizeVolume = $this->accumSizeVolume + ($this->asksBooksData[$i][1] * $this->asksBooksData[$i][3]);
            } else {
                $this->asksBooksData[$i][3] = $this->size - $accumulatedVolume;
                $this->accumSizeVolume = ($this->accumSizeVolume + ($this->asksBooksData[$i][1] * $this->asksBooksData[$i][3])) / $this->size;
                $this->asksBooksData[5][9] = "WVAP: " . $this->accumSizeVolume;
                break;
            }
        }
    }

    private function cryptoFacAsteriskFill($limitOrderCorrection){
        // Correction can be negative. In this case wrong row is added to the table
        if($limitOrderCorrection <= 5 && $limitOrderCorrection > 0){
            $this->asksBooksData[10 - $limitOrderCorrection * 2][7] = $this->size; // $this->size
        } else{
            $this->asksBooksData[9][7] = $this->size; // If correction is more than 5 - we can not show it in the table, we have 0.5 * 2 rows.
        }
    }
}
