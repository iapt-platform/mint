<?php

require dirname(__FILE__) . '/vendor/autoload.php';

class Greeter extends \Mint\Tulip\V1\SearchStub
{
    public function Pali(
        \Mint\Tulip\V1\SearchRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\SearchResponse {
        $text = $request->getPayload();
        echo 'Received request: ' . $text . PHP_EOL;
        $response = new \Mint\Morus\V1\MarkdownToHtmlResponse();
        $Parsedown = new Parsedown();
        $response->setPayload($Parsedown->text($text));
        return $response;
    }
}

$param = getopt('p:');

if (!isset($param['p'])) {
    echo 'parameter port is required. -p 9999  ';
    return;
}
$port = $param['p'];
$server = new \Grpc\RpcServer();
$server->addHttp2Port('0.0.0.0:' . $port);
$server->handle(new Greeter());
echo 'Listening on port :' . $port . PHP_EOL;
$server->run();
