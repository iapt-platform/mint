<?php

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/lib/lily/Tex.php';
require_once __DIR__ . '/lib/lily/TexToPdfTask.php';


use Monolog\Level;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Thrift\Serializer\TBinarySerializer;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;


use lily\Tex;
use lily\TexToPdfTask;

function publish_tex_to_pdf($logger, $channel, $queue, $callback)
{
    $task = new TexToPdfTask();
    $task->bucket = "testing";
    $task->object = date("Y-m-d") . '.pdf';
    $task->callback = $callback;
    $task->tex = new Tex();
    $task->tex->homepage = <<<TEX
\\documentclass{article}

\\begin{document}

\\title{The title \\thanks{With footnote}}
\\auhtor{me}
\\date{\\today}
\\maketitle

\\include{vocabulary/session-1.tex}
\\include{vocabulary/session-2.tex}
\\include{vocabulary/session-3.tex}

\\end {document}
TEX;
    $task->tex->files = array(
        "section-1.tex" => <<<TEX
\\section{Session 1}
TEX,
        "section-2.tex" => <<<TEX
\\section{Session 2}        

\\First\\footnote{First note}
\\Second\\footnote{Second note}
\\Third\\footnote{Third note}
TEX,
        "section-3.tex" => <<<TEX
\\section{Session 3}
TEX,
    );

    $body = TBinarySerializer::serialize($task);
    $message_id = uniqid();
    $logger->debug("publish a tex-to-pdf task(" . strlen($body)  . "bytes) " . $message_id);
    // https://github.com/php-amqplib/php-amqplib/blob/master/doc/AMQPMessage.md
    $message = new AMQPMessage($body, ['message_id' => $message_id]);
    $channel->basic_publish($message, '', $queue);
}

$logger = new Logger('lily');
$logger->pushHandler(new StreamHandler('php://stdout', Level::Debug));
$logger->debug("run on debug mode");

if ($argc !== 2) {
    $logger->error("usage: php $argv[0] config.yml");
    exit(1);
}
$logger->debug("$argc");
$logger->info("load config from" . $argv[1]);
$config = yaml_parse_file($argv[1]);
$logger->debug("connect to rabbitmq://" . $config['rabbitmq']['host'] . "@" . $config['rabbitmq']['host'] . ":" . $config['rabbitmq']['port'] . "/" . $config['rabbitmq']['virtual-host']);

$queue_connection = new AMQPStreamConnection($config['rabbitmq']['host'], $config['rabbitmq']['port'], $config['rabbitmq']['user'], $config['rabbitmq']['password'], $config['rabbitmq']['virtual-host']);
$queue_channel = $queue_connection->channel();


publish_tex_to_pdf($logger, $queue_channel, $config['lily']['tex-to-pdf']['queue-name'], $config['lily']['tex-to-pdf']['callback-url']);


$queue_channel->close();
$queue_connection->close();
$logger->warning("quit.");
