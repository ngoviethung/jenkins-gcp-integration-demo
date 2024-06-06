<?php

namespace App\Services;

use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Connection\AMQPSSLConnection;
use PhpAmqpLib\Message\AMQPMessage;
use Log;
use App\Http\Controllers\MergeImageController;
use App\Http\Controllers\Api\ChallengeController;
use PhpAmqpLib\Exception\AMQPProtocolChannelException;
class RabbitMQService
{
    public function publish($message, $severity)
    {
        try {
            if(!$message or !$severity){
                return 0;
            }

            $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'));
            $channel = $connection->channel();
            $channel->queue_declare($severity, false, true, false, false);

            $msg = new AMQPMessage($message, ['delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT]);
            $ttl = 60 * 1000; //60 seconds
            $msg->set('expiration', strval($ttl));

            $channel->basic_publish($msg, '', $severity);
            //echo " [x] Sent $message to $severity\n";

            $channel->close();
            $connection->close();

            return 1;
        } catch (\Exception $exception) {
            return 0;
        }

    }
    public function consume($severity)
    {
        $connection = new AMQPStreamConnection(env('MQ_HOST'), env('MQ_PORT'), env('MQ_USER'), env('MQ_PASS'));
        $channel = $connection->channel();
        try {
            $channel->queue_declare($severity, true, true, false, false);
        } catch (\Exception $e) {
            $channel->close();
            $connection->close();
            //echo "[*] Waiting for queue... \n";
            sleep(5);
            $this->consume($severity);
            return;
        }
        echo " [*] Waiting for messages. To exit press CTRL+C\n";
        //Log::info(" [*] Waiting for messages. To exit press CTRL+C\n");
        $callback = function ($msg) use ($severity, $channel) {
            echo " [x] Received $msg->body from $severity\n";

            $process = ($severity == 'vote') ? new ChallengeController() : new MergeImageController();
            $status = ($severity == 'vote') ? $process->vote($msg->body) : $process->mergeAndZipImage($msg->body);

            if ($status == 1) {
                $channel->basic_ack($msg->delivery_info['delivery_tag']);
            } else {
                $body = json_decode($msg->body, true);
                $requeue = isset($body['requeue']) ? $body['requeue'] : 0;

                if ($requeue >= 2) {
                    $channel->basic_reject($msg->delivery_info['delivery_tag'], false);
                } else {
                    $body['requeue'] = $requeue + 1;
                    $this->publish(json_encode($body), $severity);
                    $channel->basic_ack($msg->delivery_info['delivery_tag']);
                }
            }
        };

        $channel->basic_consume($severity, '', false, false, false, false, $callback);
        try {
            $channel->consume();
        } catch (\Throwable $e) {
            echo $e->getMessage();
            Log::info($e->getMessage());
        }
    }

}
