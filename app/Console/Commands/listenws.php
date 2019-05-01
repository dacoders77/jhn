<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;

class listenws extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'listen';

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
        // @see https://mattstauffer.com/blog/advanced-input-output-with-artisan-commands-tables-and-progress-bars-in-laravel-5.1/
        $headers = ['Name', 'Awesomeness Level'];
        // Note: the following would work as well:
        $data = [
            ['Jim', 'Meh'],
            ['Conchita', 'Fabulous']
        ];
        // $this->table($headers, $data);

        /**
         * Ratchet/pawl websocket library
         * @see https://github.com/ratchetphp/Pawl
         */
        $loop = \React\EventLoop\Factory::create();
        $reactConnector = new \React\Socket\Connector($loop, [
            'dns' => '8.8.8.8',
            'timeout' => 10
        ]);

        $connector = new \Ratchet\Client\Connector($loop, $reactConnector);
        // \App\Classes\Trading\DerebitWsListener::subscribe($connector, $loop, $this); // Leg 1
        // \App\Classes\Trading\CryptofacWsListener::subscribe($this); // Leg 2. Only order book update

        //CryptofacWs::dispatch();
        //DerebitWs::dispatch();
        Artisan::queue('one');;
        //Artisan::queue('two')->onQueue(env("DB_DATABASE"));;

        while (true){
            dump('hello from XXX JOB');
            sleep(1);
        }

    }
}
