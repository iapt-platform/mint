<?php
namespace App\Tools;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class Markdown
{
    public static function driver($driver){
        $GLOBALS['markdown.driver'] = $driver;
    }
    public static function render($text){
        if(isset($GLOBALS['markdown.driver']) &&
            $GLOBALS['markdown.driver'] === 'str'){
            return Markdown::strdown($text);
        }else{
            return Markdown::strdown($text);
        }
    }
    public static function morus_restful($text){
        $host = config('mint.server.rpc.morus.host');
        Log::debug('morus host='.$host);

        $response = Http::post($host, [
            'text'=>$text
        ]);
        if($response->successful()){
            return $response->json('data');
        }else{
            Log::error('morus_restful fail markdown='.$text);
            return Str::markdown($text);
        }
    }

    public static function morus($text){

        if(isset($GLOBALS['morus_client'])){
            $client = $GLOBALS['morus_client'];
        }else{
            $host = config('mint.server.rpc.morus.host') . ':'. config('mint.server.rpc.morus.port');
            Log::debug('morus host='.$host);
            $client = new \Mint\Morus\V1\MarkdownClient($host, [
                    'credentials' => \Grpc\ChannelCredentials::createInsecure(),
                ]);
            $GLOBALS['morus_client'] = $client;
        }

        $request = new \Mint\Morus\V1\MarkdownToHtmlRequest();
        $request->setPayload($text);
        $request->setSanitize(true);
        list($response, $status) = $client->ToHtml($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("ERROR: " . $status->code . ", " . $status->details);
            return Str::markdown($text);
        }
        return $response->getPayload();
    }

    public static function strdown($text){
        $text = str_replace("** ","**\r\n ",$text);
        return Str::markdown($text);
    }
}
