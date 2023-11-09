<?php
namespace App\Tools;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class Markdown
{
    public static function driver($driver){
        switch ($driver) {
            case 'morus':
                $GLOBALS['markdown.driver'] = 'morus';
                break;
            default:
                unset($GLOBALS['markdown.driver']);
                break;
        }
    }
    public static function render($text){
        if(isset($GLOBALS['markdown.driver'])){
            if($GLOBALS['markdown.driver'] === 'morus'){
                return Markdown::morus($text);
            }else{
                return Markdown::strdown($text);
            }
        }else{
            return Markdown::strdown($text);
        }
    }

    public static function morus($text){
        $host = config('mint.server.rpc.morus.host') . ':'. config('mint.server.rpc.morus.port');
        Log::debug('morus host='.$host);
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
