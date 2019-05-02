<?php

namespace App\Console\Commands;

use App\Classes\Logic\Delete;
use App\Classes\Logic\OrderBooksWatch;
use App\Classes\WebSocketStream;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Ratchet\Client\WebSocket;

// LEG1 HEDGING

class derebit extends Command
{
    protected $connection;
    private $orderBooksWatch;
    public static $isDerebitOrderBookReceived;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'derebit';

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
        $this->orderBooksWatch = new OrderBooksWatch($this);
        Cache::put('cryptoFac', null, now()->addMinute(5));

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

        /**
         * Derebit Websocket end point
         * @see https://docs.deribit.com/v2/#deribit-api-v2-0-0
         */
        $exchangeWebSocketEndPoint = "wss://www.deribit.com/ws/api/v2/";

        $connector($exchangeWebSocketEndPoint, [], ['Origin' => 'http://localhost'])
            ->then(function(\Ratchet\Client\WebSocket $conn) use ($loop) {
                $this->connection = $conn; // In order to use $conn outside of this function
                $conn->on('message', function(\Ratchet\RFC6455\Messaging\MessageInterface $socketMessage) use ($conn, $loop) {
                    $jsonMessage = json_decode($socketMessage->getPayload(), true);

                    if (array_key_exists('params', $jsonMessage))
                        if (array_key_exists('data', $jsonMessage['params']))
                            if (array_key_exists('bids', $jsonMessage['params']['data'])){
                                dump($jsonMessage);
                                Cache::put('isDerebitOrderBookReceived', true, now()->addDay(1));
                                Cache::put('derebitBook', $jsonMessage, now()->addMinute(1));
                                $this->orderBooksWatch->check("derebit");
                            }


                });

                $conn->on('close', function($code = null, $reason = null) use ($loop) {
                    echo "Connection closed ({$code} - {$reason})\n";
                    $this->info("line 82. connection closed");
                    $this->error("Reconnecting back!");
                    sleep(5); // Wait 5 seconds before next connection try will attempt
                    $this->handle(); // Call the main method of this class
                });

                /* Manual subscription object. If cache subscription used - disable this */
                $requestObject = json_encode([
                    "method" => "public/subscribe",
                    "params"=> [
                        "channels" => ["book.BTC-PERPETUAL.none.10.100ms"]
                    ],
                    "jsonrpc" => "2.0",
                    "id" => 35
                ]);
                $conn->send($requestObject);

            }, function(\Exception $e) use ($loop) {
                $errorString = "RatchetPawlSocket.php Could not connect. Reconnect in 5 sec. \n Reason: {$e->getMessage()} \n";
                echo $errorString;
                sleep(5); // Wait 5 seconds before next connection try will attpemt
                $this->handle(); // Call the main method of this class
                //$loop->stop();
            });
        $loop->run();
    }
}
