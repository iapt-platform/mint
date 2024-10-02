<?php

require_once dirname(__FILE__) . '/vendor/autoload.php';
require_once dirname(__FILE__) . '/config.php';
require_once dirname(__FILE__) . '/console.php';
require_once dirname(__FILE__) . '/pdo.php';
require_once dirname(__FILE__) . '/log.php';



class Greeter extends \Mint\Tulip\V1\SearchStub
{

    public function Pali(
        \Mint\Tulip\V1\SearchRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\SearchResponse {
        $keyWords = [];
        foreach ($request->getKeywords()->getIterator() as $word) {
            $keyWords[] = $word;
        }

        if ($request->hasPage()) {
            $limit = $request->getPage()->getSize();
            $offset = $request->getPage()->getIndex();
        } else {
            $limit = 10;
            $offset = 0;
        }

        $matchMode = $request->getMatchMode();



        $bookId = [];
        if ($request->getBooks()->count() > 0) {
            foreach ($request->getBooks()->getIterator() as $book) {
                $bookId[] = $book;
            }
            $queryBookId = ' AND pcd_book_id in (' . implode(',', $bookId) . ') ';
        } else {
            $queryBookId = '';
        }

        myLog()->info('request',[
            'keyWords'=>$keyWords,
            'limit'=>$limit,
            'offset'=>$offset,
            'mode'=>$matchMode,
            'books'=>implode(',', $bookId),
        ]);
        
        $pdo = new PdoHelper;
        $pdo->connectDb();
        /**
         * 查询业务逻辑
         */
        $param = [];
        switch ($matchMode) {
            case 'complete':
            case 'case':
                # code...
                $querySelect_rank_base = " ts_rank('{0.1, 1, 0.3, 0.2}',
                                                full_text_search_weighted,
                                                websearch_to_tsquery('pali', ?)) ";
                $querySelect_rank_head = implode(
                    '+',
                    array_fill(
                        0,
                        count($keyWords),
                        $querySelect_rank_base
                    )
                );

                $param = array_merge($param, $keyWords);
                $querySelect_rank = " {$querySelect_rank_head} AS rank, ";
                $querySelect_highlight = " ts_headline('pali', content,
                                            websearch_to_tsquery('pali', ?),
                                            'StartSel = ~~, StopSel = ~~,MaxWords=3500, 
                                            MinWords=3500,HighlightAll=TRUE')
                                            AS highlight,";
                array_push($param, implode(' ', $keyWords));
                break;
            case 'similar':
                # 形似，去掉变音符号
                $key = $this->getWordEn($keyWords[0]);
                $querySelect_rank = "
                    ts_rank('{0.1, 1, 0.3, 0.2}',
                        full_text_search_weighted_unaccent,
                        websearch_to_tsquery('pali_unaccent', ?))
                    AS rank, ";
                $param[] = $key;
                $querySelect_highlight = " ts_headline('pali_unaccent', content,
                        websearch_to_tsquery('pali_unaccent', ?),
                        'StartSel = ~~, StopSel = ~~,
                        MaxWords=3500, MinWords=3500,
                        HighlightAll=TRUE')
                        AS highlight,";
                $param[] = $key;
                break;
        }
        $_queryWhere = $this->makeQueryWhere($keyWords, $matchMode);
        $queryWhere = $_queryWhere['query'];
        $param = array_merge($param, $_queryWhere['param']);

        $querySelect_2 = "  book,paragraph,content ";

        $queryCount = "SELECT count(*) as co FROM fts_texts WHERE {$queryWhere} {$queryBookId};";

        myLog()->debug('pali queryCount',['sql'=>$queryCount,'param'=>$_queryWhere['param']]);

        $resultCount = $pdo->dbSelect($queryCount, $_queryWhere['param']);
        if (
            is_array($resultCount) &&
            count($resultCount) > 0 &&
            isset($resultCount[0]['co'])
        ) {
            $total = $resultCount[0]['co'];
        } else {
            myLog()->error('result must be of type array' . $pdo->errorInfo());
            $total = 0;
        }

        myLog()->info("pali result total={$total}");

        $_orderBy = 'rank';
        switch ($_orderBy) {
            case 'rank':
                $orderby = " ORDER BY rank DESC ";
                break;
            case 'paragraph':
                $orderby = " ORDER BY book,paragraph ";
                break;
            default:
                $orderby = "";
                break;
        };
        $query = "SELECT
            {$querySelect_rank}
            {$querySelect_highlight}
            {$querySelect_2}
            FROM fts_texts
            WHERE
                {$queryWhere}
                {$queryBookId}
                {$orderby}
            LIMIT ? OFFSET ? ;";
        $param[] = $limit;
        $param[] = $offset;


        $result = $pdo->dbSelect($query, $param);
        //返回数据
        $response = new \Mint\Tulip\V1\SearchResponse();
        $output = $response->getItems();

        if ($result !== false) {
            myLog()->debug('query result count='.count($result));
            foreach ($result as $row) {
                $item = new \Mint\Tulip\V1\SearchResponse\Item;
                $item->setRank($row['rank']);
                $item->setHighlight($row['highlight']);
                $item->setBook($row['book']);
                $item->setParagraph($row['paragraph']);
                $item->setContent($row['content']);
                $output[] = $item;
            }
        } else {
            myLog()->error("result is false");
        }

        $response->setTotal($total);
        $page = new \Mint\Tulip\V1\SearchRequest\Page;
        $page->setIndex($offset);
        $page->setSize($limit);
        $response->setPage($page);
        return $response;
    }

    /**
     * @param \Mint\Tulip\V1\SearchRequest $request client request
     * @param \Grpc\ServerContext $context server request context
     * @return \Mint\Tulip\V1\BookListResponse for response data, null if if error occured
     *     initial metadata (if any) and status (if not ok) should be set to $context
     */
    public function BookList(
        \Mint\Tulip\V1\SearchRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\BookListResponse {
        $keyWords = [];
        foreach ($request->getKeywords()->getIterator() as $word) {
            $keyWords[] = $word;
        }
        
        /**
         * 查询业务逻辑
         */
        $pdo = new PdoHelper;
        $pdo->connectDb();

        myLog()->debug('db connected');

        $bookId = [];
        if ($request->getBooks()->count() > 0) {
            foreach ($request->getBooks()->getIterator() as $book) {
                $bookId[] = $book;
            }
            $queryBookId = ' AND pcd_book_id in (' . implode(',', $bookId) . ') ';
        } else {
            $queryBookId = '';
        }
        myLog()->info("book list: request ",[
            'words'=>implode(',', $keyWords),
            'books'=>implode(',', $bookId),
            ] );

        $matchMode = $request->getMatchMode();
        myLog()->debug('query mode = ' . $matchMode);
        $queryWhere = $this->makeQueryWhere($keyWords, $matchMode);
        $query = "SELECT pcd_book_id, count(*) as co FROM fts_texts WHERE {$queryWhere['query']} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
        myLog()->debug('book list queryCount',['sql'=>$query,'param'=>$queryWhere['param']]);
        $result = $pdo->dbSelect($query, $queryWhere['param']);
        //返回数据
        $response = new \Mint\Tulip\V1\BookListResponse();
        $output = $response->getItems();
        if ($result) {
            foreach ($result as $row) {
                $item = new \Mint\Tulip\V1\BookListResponse\Item;
                $item->setBook($row['pcd_book_id']);
                $item->setCount($row['co']);
                $output[] = $item;
            }
        }
        myLog()->debug("book list total=" . count($output));
        return $response;
    }


    private function makeQueryWhere($key, $match)
    {
        $param = [];
        $queryWhere = '';
        switch ($match) {
            case 'complete':
            case 'case':
                # code...
                $queryWhereBase = " full_text_search_weighted @@ websearch_to_tsquery('pali', ?) ";
                $queryWhereBody = implode(' or ', array_fill(
                    0,
                    count($key),
                    $queryWhereBase
                ));
                $queryWhere = " ({$queryWhereBody}) ";
                $param = array_merge($param, $key);
                break;
            case 'similar':
                # 形似，去掉变音符号
                $queryWhere = " full_text_search_weighted_unaccent @@ websearch_to_tsquery('pali_unaccent', ?) ";
                $key = $this->getWordEn($key[0]);
                $param = [$key];
                break;
        };
        return (['query' => $queryWhere, 'param' => $param]);
    }

    private function getWordEn($strIn)
    {
        $out = str_replace(
            ["ā", "ī", "ū", "ṅ", "ñ", "ṭ", "ḍ", "ṇ", "ḷ", "ṃ"],
            ["a", "i", "u", "n", "n", "t", "d", "n", "l", "m"],
            $strIn
        );
        return ($out);
    }
}

$param = getopt('d::');
if(isset($param['d'])){
    echo 'debug mode'.PHP_EOL;
    $GLOBALS['debug'] = true;
}

if (!isset(Config['port'])) {
    myLog()->error('parameter port is required.');
    return;
}

$server = new \Grpc\RpcServer();
$server->addHttp2Port('0.0.0.0:' . Config['port']);
$server->handle(new Greeter());
myLog()->info('Listening on port :' . Config['port']);
$server->run();
