<?php

require dirname(__FILE__) . '/vendor/autoload.php';

function md2htm($host, $text)
{
    $client = new Mint\Morus\V1\MarkdownClient($host, [
        'credentials' => Grpc\ChannelCredentials::createInsecure(),
    ]);
    $request = new Mint\Morus\V1\MarkdownToHtmlRequest();
    $request->setPayload($text);
    $request->setSanitize(true);
    list($response, $status) = $client->ToHtml($request)->wait();
    if ($status->code !== Grpc\STATUS_OK) {
        echo "ERROR: " . $status->code . ", " . $status->details . PHP_EOL;
        exit(1);
    }
    echo $response->getPayload() . PHP_EOL;
}

md2htm('localhost:9999', '# Hi, mint!');
