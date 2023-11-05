<?php
namespace App\Tools;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaliSearch
{
    public static function search($words,$book,$index,$size){
        $host = config('mint.server.rpc.tulip');
        Log::debug('tulip host='.$host);
        $client = new \Mint\Tulip\V1\SearchClient($host, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);

        $request = new \Mint\Tulip\V1\SearchRequest();
        $request->setKeywords($words);
        $request->setBook($book);
        $page = new \Mint\Tulip\V1\SearchRequest\Page;
        $page->setIndex($index);
        $page->setSize($size);
        $request->setPage($page);

        list($response, $status) = $client->Pali($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("ERROR: " . $status->code . ", " . $status->details);
            return false;
        }
        return $response->getItems();
    }


}
