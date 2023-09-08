<?php
namespace App\Tools;
use Illuminate\Support\Str;

class Markdown
{
    public static function render($text){
        return Markdown::morus($text);
    }

    public static function morus($text){
        $host = env('MORUS_RPC_SERVER');
        $client = new \Mint\Morus\V1\MarkdownClient($host, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
        $request = new \Mint\Morus\V1\MarkdownToHtmlRequest();
        $request->setPayload($text);
        $request->setSanitize(true);
        list($response, $status) = $client->ToHtml($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            return "ERROR: " . $status->code . ", " . $status->details;
        }
        return $response->getPayload();
    }

    public static function strdown($text){
        return Str::markdown($text);
    }
}
