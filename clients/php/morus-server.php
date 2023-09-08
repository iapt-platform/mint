<?php

require dirname(__FILE__) . '/vendor/autoload.php';

class Greeter extends \Mint\Morus\V1\MarkdownStub
{
    public function ToHtml(
        \Mint\Morus\V1\MarkdownToHtmlRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Morus\V1\MarkdownToHtmlResponse {
        $text = $request->getPayload();
        echo 'Received request: ' . $text . PHP_EOL;
        $response = new \Mint\Morus\V1\MarkdownToHtmlResponse();
        $response->setPayload("Hello <h1>" . $text . "</h1>");
        return $response;
    }
}

$port = 9999;
$server = new \Grpc\RpcServer();
$server->addHttp2Port('0.0.0.0:' . $port);
$server->handle(new Greeter());
echo 'Listening on port :' . $port . PHP_EOL;
$server->run();
