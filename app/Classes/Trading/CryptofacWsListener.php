<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/17/2019
 * Time: 9:15 PM
 */

namespace App\Classes\Trading;
use App\Classes\Logic\OrderBookToArray;
use Ratchet\Client\WebSocket;

class CryptofacWsListener {

    public static $console;
    public static function subscribe($console){
        self::$console = $console;

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
         * Cryptofac Websocket end point
         * @see https://www.cryptofacilities.com/resources/hc/en-us/articles/360018976314-Conformance-Testing-Environment
         */
        $exchangeWebSocketEndPoint = "wss://conformance.cryptofacilities.com/ws/v1";

        $connector($exchangeWebSocketEndPoint, [], ['Origin' => 'http://localhost'])
            ->then(function(\Ratchet\Client\WebSocket $conn) use ($loop) {
                $conn->on('message', function(\Ratchet\RFC6455\Messaging\MessageInterface $socketMessage) use ($conn, $loop) {
                    $jsonMessage = json_decode($socketMessage->getPayload(), true);

                    if (array_key_exists('bids', $jsonMessage)) {
                        OrderBookToArray::parse($jsonMessage['bids']);
                    }
                    if (array_key_exists('feed', $jsonMessage) && array_key_exists('side', $jsonMessage)) {
                        if ($jsonMessage['feed'] == 'book') OrderBookToArray::update($jsonMessage);
                    }

                });

                $conn->on('close', function($code = null, $reason = null) use ($loop) {
                    echo "Connection closed ({$code} - {$reason})\n";
                    self::$console->info("Connection closed " . __LINE__);
                    self::$console->error("Reconnecting back!");
                    sleep(5); // Wait 5 seconds before next connection try will attempt
                    self::$console->handle(); // Call the main method of this class
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
                self::subscribe(self::$console); // Call the main method of this class
                //$loop->stop();
            });
        $loop->run();
    }
}