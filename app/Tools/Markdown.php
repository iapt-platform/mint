<?php
namespace App\Tools;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Markdown
{
    public static function render($text){
        return Markdown::strdown($text);
    }

    public static function morus($text){
        $host = config('morus.rpc.server');
        $client = new \Mint\Morus\V1\MarkdownClient($host, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
        $request = new \Mint\Morus\V1\MarkdownToHtmlRequest();
        $request->setPayload($text);
        $request->setSanitize(true);
        list($response, $status) = $client->ToHtml($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("ERROR: " . $status->code . ", " . $status->details);
            return $text;
        }
        return $response->getPayload();
    }

    public static function strdown($text){
        return Str::markdown($text);
    }
}
