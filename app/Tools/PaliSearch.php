<?php
namespace App\Tools;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaliSearch
{
    public static function search($words,$book,$page){
        $host = config('mint.server.rpc.tulip');
        Log::debug('tulip host='.$host);
        $client = new \Mint\Tulip\V1\SearchClient($host, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
        $request = new \Mint\Tulip\V1\SearchRequest();
        $request->setKeywords($words);
        $request->setBook($book);
        $request->setPage($page);

        list($response, $status) = $client->Pali($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("ERROR: " . $status->code . ", " . $status->details);
            return $text;
        }
        return $response->getPayload();
    }

}
