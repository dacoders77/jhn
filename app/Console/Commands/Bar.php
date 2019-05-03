<?php

namespace App\Console\Commands;

use App\Classes\Logic\Delete;
use App\Classes\Logic\OrderBooksWatch;
use App\Classes\WebSocketStream;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Ratchet\Client\WebSocket;
use Symfony\Component\Console\Helper\Table;

class Bar extends Command
{
    protected $connection;
    private $orderBooksWatch;
    public static $isDerebitOrderBookReceived;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bar';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'BTMX ratchet/pawl ws client console application';

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
        //$bar->start();
        $i = 0;
        while ($i++ < 500) {
            // ... do some work

            // advances the progress bar 1 unit
            $bar->advance();
            usleep(30000);
            // you can also advance the progress bar by more than 1 unit
            // $progressBar->advance(3);
        }

        /*$bar = $this->output->createProgressBar(5);
        //$bar->start();
        $i = 0;
        while ($i++ < 5) {
            // ... do some work

            // advances the progress bar 1 unit
            //$bar->advance();
            sleep(1);
            // you can also advance the progress bar by more than 1 unit
            // $progressBar->advance(3);
        }*/
    }
}
