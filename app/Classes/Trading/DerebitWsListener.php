<?php
/**
 * Created by PhpStorm.
 * User: slinger
 * Date: 4/22/2019
 * Time: 1:02 PM
 */

namespace App\Classes\Trading;

class DerebitWsListener
{
    public static $console;
    public static function subscribe($connector, $loop, $console){
        self::$console = $console;
        /**
         * Derebit Websocket end point
         * @see https://docs.deribit.com/v2/#deribit-api-v2-0-0
         */
        $exchangeWebSocketEndPoint = "wss://www.deribit.com/ws/api/v2/";
        $connector($exchangeWebSocketEndPoint, [], ['Origin' => 'http://localhost'])
            ->then(function(\Ratchet\Client\WebSocket $conn) use ($loop) {
                $conn->on('message', function(\Ratchet\RFC6455\Messaging\MessageInterface $socketMessage) use ($conn, $loop) {
                    $jsonMessage = json_decode($socketMessage->getPayload(), true);
                    dump('derebit');
                    //dump($jsonMessage);
                    /*if (array_key_exists('data', $jsonMessage)){
                        if (array_key_exists('lastPrice', $jsonMessage['data'][0])){
                            WebSocketStream::Parse($jsonMessage['data']); // Update quotes, send events to vue
                            WebSocketStream::stopLossCheck($jsonMessage['data']); // Stop loss execution
                        }
                    }*/
                });
                $conn->on('close', function($code = null, $reason = null) use ($loop) {
                    echo "Connection closed ({$code} - {$reason})\n";
                    self::$console->info("line 82. connection closed");
                    self::$console->error("Reconnecting back!");
                    sleep(5); // Wait 5 seconds before next connection try will attempt
                    self::$console->subscribe(); // Call the main method of this class
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
                self::subscribe(self::$console); // Call the main method of this class
                //$loop->stop();
            });
        $loop->run();
    }
}