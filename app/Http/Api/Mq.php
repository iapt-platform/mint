<?php
namespace App\Http\Api;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;
class Mq{
    public static function publish(string $channelName, $message){
                //一对一
		$connection = new AMQPStreamConnection(env("MQ_HOST"), env("MQ_PORT"), env("MQ_USERNAME"), env("MQ_PASSWORD"));
		$channel = $connection->channel();
		$channel->queue_declare($channelName, false, true, false, false);

		$msg = new AMQPMessage(json_encode($message,JSON_UNESCAPED_UNICODE));
		$channel->basic_publish($msg, '', $channelName);

		$channel->close();
		$connection->close();
    }
}
