<?php

namespace App\Tools;

use Grpc\ChannelCredentials;
use Illuminate\Support\Facades\Log;
use Mint\Tulip\V1\SearchClient;
use Mint\Tulip\V1\SearchRequest;
use Mint\Tulip\V1\SearchRequest\Page;
use Mint\Tulip\V1\UpdateRequest;

class PaliSearch
{
    public static function connect()
    {
        $host = config('mint.server.rpc.tulip.host').':'.config('mint.server.rpc.tulip.port');
        $client = new SearchClient($host, [
            'credentials' => ChannelCredentials::createInsecure(),
        ]);

        return $client;
    }

    public static function search($words, $books, $matchMode = 'case', $index = 0, $size = 10)
    {
        $client = PaliSearch::connect();
        $request = new SearchRequest;
        $request->setKeywords($words);
        $request->setBooks($books);
        $request->setMatchMode($matchMode);
        $page = new Page;
        $page->setIndex($index);
        $page->setSize($size);
        $request->setPage($page);

        [$response, $status] = $client->Pali($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error('ERROR: '.$status->code.', '.$status->details);

            return false;
        }
        $output = [];
        $output['total'] = $response->getTotal();
        $output['page'] = $response->getPage();
        $output['rows'] = [];
        foreach ($response->getItems() as $key => $value) {
            $output['rows'][] = (object) [
                'rank' => $value->getRank(),
                'highlight' => $value->getHighlight(),
                'book' => $value->getBook(),
                'paragraph' => $value->getParagraph(),
                'content' => $value->getContent(),
            ];
        }

        return $output;
    }

    public static function book_list($words, $books, $matchMode = 'case', $index = 0, $size = 10)
    {
        $client = PaliSearch::connect();

        $request = new SearchRequest;
        $request->setKeywords($words);
        $request->setBooks($books);
        $request->setMatchMode($matchMode);
        $page = new Page;
        $page->setIndex($index);
        $page->setSize($size);
        $request->setPage($page);

        [$response, $status] = $client->BookList($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error('ERROR: '.$status->code.', '.$status->details);

            return false;
        }
        $output = [];
        $output['rows'] = [];
        foreach ($response->getItems() as $key => $value) {
            $output['rows'][] = (object) [
                'pcd_book_id' => $value->getBook(),
                'co' => $value->getCount(),
            ];
        }

        return $output;
    }

    public static function update($book, $paragraph,
        $bold1, $bold2, $bold3,
        $content, $pcd_book_id)
    {
        $client = PaliSearch::connect();
        $request = new UpdateRequest;
        $request->setBook($book);
        $request->setParagraph($paragraph);
        $request->setLevel(0);
        $request->setBold1($bold1);
        $request->setBold2($bold2);
        $request->setBold3($bold3);
        $request->setContent($content);
        $request->setPcdBookId($pcd_book_id);

        [$response, $status] = $client->Update($request)->wait();
        if ($status->code !== \Grpc\STATUS_OK) {
            Log::error('ERROR: '.$status->code.', '.$status->details);

            return false;
        }

        return $response->getCount();
    }
}
