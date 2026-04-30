<?php
namespace App\Tools;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PaliSearch
{
    public static function connect(){
        $host = config('mint.server.rpc.tulip.host') . ':' . config('mint.server.rpc.tulip.port');
        Log::debug('tulip host='.$host);
        $client = new \Mint\Tulip\V1\SearchClient($host, [
            'credentials' => \Grpc\ChannelCredentials::createInsecure(),
        ]);
        return $client;
    }
    public static function search($words,$books,$matchMode='case',$index=0,$size=10){
        $client = PaliSearch::connect();
        $request = new \Mint\Tulip\V1\SearchRequest();
        $request->setKeywords($words);
        $request->setBooks($books);
        $request->setMatchMode($matchMode);
        $page = new \Mint\Tulip\V1\SearchRequest\Page;
        $page->setIndex($index);
        $page->setSize($size);
        $request->setPage($page);

        list($response, $status) = $client->Pali($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("ERROR: " . $status->code . ", " . $status->details);
            return false;
        }
        $output = [];
        $output['total'] = $response->getTotal();
        $output['page'] = $response->getPage();
        $output['rows'] = [];
        foreach ($response->getItems() as $key => $value) {
            $output['rows'][] = (object)[
                'rank' => $value->getRank(),
                'highlight' => $value->getHighlight(),
                'book' => $value->getBook(),
                'paragraph' => $value->getParagraph(),
                'content' => $value->getContent(),
            ];
        }
        return $output;
    }

    public static function book_list($words,$books,$matchMode='case',$index=0,$size=10){
        $client = PaliSearch::connect();

        $request = new \Mint\Tulip\V1\SearchRequest();
        $request->setKeywords($words);
        $request->setBooks($books);
        $request->setMatchMode($matchMode);
        $page = new \Mint\Tulip\V1\SearchRequest\Page;
        $page->setIndex($index);
        $page->setSize($size);
        $request->setPage($page);

        list($response, $status) = $client->BookList($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("ERROR: " . $status->code . ", " . $status->details);
            return false;
        }
        $output = [];
        $output['rows'] = [];
        foreach ($response->getItems() as $key => $value) {
            $output['rows'][] = (object)[
                'pcd_book_id' => $value->getBook(),
                'co' => $value->getCount(),
            ];
        }
        return $output;
    }


    public static function update($book,$paragraph,
                                  $bold1,$bold2,$bold3,
                                  $content,$pcd_book_id){
        $client = PaliSearch::connect();
        Log::debug('tulip update',['book'=>$book,'paragraph'=>$paragraph]);
        $request = new \Mint\Tulip\V1\UpdateRequest();
        $request->setBook($book);
        $request->setParagraph($paragraph);
        $request->setLevel(0);
        $request->setBold1($bold1);
        $request->setBold2($bold2);
        $request->setBold3($bold3);
        $request->setContent($content);
        $request->setPcdBookId($pcd_book_id);

        list($response, $status) = $client->Update($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error("ERROR: " . $status->code . ", " . $status->details);
            return false;
        }
        Log::debug('tulip update success',['book'=>$book,'paragraph'=>$paragraph]);
        return $response->getCount();
    }
}
