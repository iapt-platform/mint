<?php

require dirname(__FILE__) . '/vendor/autoload.php';

function texlive_task_message($s3_bucket_name)
{
    $output =  new Palm\Lily\V1\TeXLiveTask\Output();
    $output->setFormat(Palm\Lily\V1\TeXLiveTask\Output\Format::Pdf);
    $output->setBucket($s3_bucket_name);
    $output->setObject(Ramsey\Uuid\Uuid::uuid4()->toString() . '.pdf');

    $request = new Palm\Lily\V1\TeXLiveTask();
    $request->setEntry(file_get_contents('tex/main.tex'));

    foreach (array("coding.png", "foreword.tex", "section-1.tex", "section-2.tex", "postscript.tex") as $f) {
        $request->getAttachments()[$f] = file_get_contents('tex/' . $f);
    }

    $request->setOutput($output);
    return $request->serializeToString();
}
function pandoc_task_message($s3_bucket_name)
{
    $input =  new Palm\Lily\V1\PandocTask\Input();
    $input->setFormat(Palm\Lily\V1\PandocTask\Format::Markdown);
    $input->setPayload("# Hello, Lily!");

    $output =  new Palm\Lily\V1\PandocTask\Output();
    $output->setFormat(Palm\Lily\V1\PandocTask\Format::Docx);
    $output->setBucket($s3_bucket_name);
    $output->setObject(Ramsey\Uuid\Uuid::uuid4()->toString() . '.docx');

    $request = new Palm\Lily\V1\PandocTask();
    $request->setInput($input);
    $request->setOutput($output);
    return $request->serializeToString();
}

function send_message($channel, $queue_name, $message_id, $message_body)
{
    global $logger;
    $logger->info("send message", ['id' => $message_id]);
    $message = new PhpAmqpLib\Message\AMQPMessage(
        $message_body,
        properties: ['message_id' => $message_id, 'content_type' => 'application/grpc+proto']
    );
    $channel->basic_publish($message, '', $queue_name);
}

$logger = new Monolog\Logger('palm');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Level::Debug));


if (empty($argv[1]) or empty($argv[2]) or empty($argv[3])) {
    $logger->error("USAGE: " . $argv[0] . " CONFIG_FILE JOB QUEUE");
    exit(1);
}

$logger->debug("load config from", ['file' => $argv[1]]);
$config = json_decode(file_get_contents($argv[1]));

$logger->info("connect to", ['host' => $config->{'host'}, 'port' => $config->{'port'}, 'user' => $config->{'user'}]);
$queue_connection = new PhpAmqpLib\Connection\AMQPStreamConnection(
    $config->{'host'},
    $config->{'port'},
    $config->{'user'},
    $config->{'password'},
    vhost: $config->{'virtual-host'}
);
$queue_channel = $queue_connection->channel();
$queue_name = $argv[3];
$logger->info("create queue", ['name' => $queue_name]);
$queue_channel->queue_declare($queue_name, false, false, false, false);

$s3_bucket_name = "testing";

foreach (range(1, 10) as $i) {
    $message_id = Ramsey\Uuid\Uuid::uuid4()->toString();

    $message_properties = [];
    switch ($argv[2]) {
        case "pandoc":
            send_message($queue_channel, $queue_name, $message_id, pandoc_task_message($s3_bucket_name));
            break;
        case "texlive":
            send_message($queue_channel, $queue_name, $message_id, texlive_task_message($s3_bucket_name));
            break;
        default:
            $logger->error("unknown job", ['name' => $argv[2]]);
            exit(1);
    }
    sleep(1);
}

$queue_channel->close();
$queue_connection->close();
