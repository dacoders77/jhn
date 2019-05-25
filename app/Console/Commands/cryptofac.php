<?php

namespace App\Console\Commands;

use App\Classes\Logic\Delete;
use App\Classes\Logic\OrderBooksWatch;
use App\Classes\Logic\OrderBookToArray;
use App\Classes\WebSocketStream;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Ratchet\Client\WebSocket;

/**
 * Sample Cryptofac connector.
 * Demonstrates websocket connection handling.
 * Not used in the code.
 *
 * Class cryptofac
 * @package App\Console\Commands
 */
class cryptofac extends Command
{
    protected $connection;
    private $orderBooksWatch;
    public static $isOrderBookReceived;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'cryptofac';

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

        /* Start subscription when the console command is started. Symbols will be taken from the DB */
        // Cache::put('object', ['subscribeInit' => true], 5);

        // $loop->addPeriodicTimer(2, function() use($loop) {
        //    \App\Classes\Websocket::listenCache($this->connection);
        // });

        $connector = new \Ratchet\Client\Connector($loop, $reactConnector);

        /**
         * Cryptofac Websocket end point
         * @see https://www.cryptofacilities.com/resources/hc/en-us/articles/360018976314-Conformance-Testing-Environment
         */

        //$exchangeWebSocketEndPoint = "wss://conformance.cryptofacilities.com/ws/v1";
        $exchangeWebSocketEndPoint = "wss://www.cryptofacilities.com/ws/v1";

        $connector($exchangeWebSocketEndPoint, [], ['Origin' => 'http://localhost'])
            ->then(function(\Ratchet\Client\WebSocket $conn) use ($loop) {
                $this->connection = $conn; // In order to use $conn outside of this function
                $conn->on('message', function(\Ratchet\RFC6455\Messaging\MessageInterface $socketMessage) use ($conn, $loop) {
                    $jsonMessage = json_decode($socketMessage->getPayload(), true);
                    dump($jsonMessage);

                    if (array_key_exists('bids', $jsonMessage)) {
                        OrderBookToArray::parse($jsonMessage);
                        Cache::put('isCryptoFacOrderBookReceived', true, now()->addDay(1));

                    }
                    if (array_key_exists('feed', $jsonMessage) && array_key_exists('side', $jsonMessage)) {

                        if ($jsonMessage['feed'] == 'book') OrderBookToArray::update($jsonMessage);

                        $this->orderBooksWatch->check('cryptoFac');
                        Cache::put('cryptoFacBook', OrderBookToArray::update($jsonMessage), now()->addMinute(1));


                        //if ($jsonMessage['feed'] == 'book') dump(OrderBookToArray::update($jsonMessage));
                    }
                });

                $conn->on('close', function($code = null, $reason = null) use ($loop) {
                    echo "Connection closed ({$code} - {$reason})\n";
                    $this->info("line 82. connection closed");
                    $this->error("Reconnecting back!");
                    sleep(5); // Wait 5 seconds before next connection try will attempt
                    $this->handle(); // Call the main method of this class
                });

                /* Manual subscription object. If on - subscription at the start must be disabled */
                $requestObject = json_encode([
                    "event" => "subscribe",
                    "feed" => "book",
                    "product_ids" => ["PI_XBTUSD"] // ["PI_XBTUSD","PI_XBTUSD_VOLATILE"]
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
