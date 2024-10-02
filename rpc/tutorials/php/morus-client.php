<?php

require dirname(__FILE__) . '/vendor/autoload.php';

function markdown2html($server_uri, $options, $payload, $sanitize)
{
    global $logger;

    $client = new Palm\Morus\V1\MarkdownClient($server_uri, $options);
    $request = new Palm\Morus\V1\MarkdownToHtmlRequest();
    $request->setPayload($payload);
    $request->setSanitize($sanitize);
    list($response, $status) = $client->ToHtml($request)->wait();
    if ($status->code !== Grpc\STATUS_OK) {
        $logger->error("markdown to html", ['code' => $status->code, 'details' => $status->details]);
        exit(1);
    }
    $logger->info("markdown to html", ['body' => $response->getPayload()]);
}

$logger = new Monolog\Logger('palm');
$logger->pushHandler(new Monolog\Handler\StreamHandler('php://stdout', Monolog\Level::Debug));


if (empty($argv[1])) {
    $logger->error("USAGE: " . $argv[0] . " CONFIG_FILE");
    exit(1);
}

$logger->debug("load config from", ['file' => $argv[1]]);
$config = json_decode(file_get_contents($argv[1]));

$server_uri = $config->{'server'}->{'host'} . ":" . $config->{'server'}->{'port'};
// $server_options = [
//     'credentials' => Grpc\ChannelCredentials::createInsecure(),
// ];
$server_options = [
    'credentials' => \Grpc\ChannelCredentials::createSsl(
        file_get_contents($config->{'ssl'}->{'ca-file'}),
        file_get_contents($config->{'ssl'}->{'key-file'}),
        file_get_contents($config->{'ssl'}->{'cert-file'})
    ),
    // 'grpc.ssl_target_name_override' => $config->{'server'}->{'host'},
    'grpc.default_authority' => $config->{'ssl'}->{'authority'}
];

$logger->info("connect to", ['server' => $server]);
markdown2html($server_uri, $server_options, "# Hi, Palm!", true);
