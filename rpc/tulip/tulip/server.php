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
        $msg = "[" . date("Y/m/d h:i:sa") . "] pali search: request words = " . implode(',', $keyWords);
        console('debug', $msg);
        myLog()->info($msg);

        $pdo = new PdoHelper;
        $pdo->connectDb();
        /**
         * 查询业务逻辑
         */

        $bookId = [];
        if ($request->getBooks()->count() > 0) {
            foreach ($request->getBooks()->getIterator() as $book) {
                $bookId[] = $book;
            }
            $queryBookId = ' AND pcd_book_id in (' . implode(',', $bookId) . ') ';
        } else {
            $queryBookId = '';
        }
        $msg = 'query books = ' . implode(',', $bookId);
        console('debug', $msg);
        myLog()->info($msg);

        $matchMode = $request->getMatchMode();
        console('debug', 'query mode = ' . $matchMode);
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
        $resultCount = $pdo->dbSelect($queryCount, $_queryWhere['param']);
        if (
            is_array($resultCount) &&
            count($resultCount) > 0 &&
            isset($resultCount[0]['co'])
        ) {
            $total = $resultCount[0]['co'];
        } else {
            console('debug', 'warning', 'result must be of type array' . $pdo->errorInfo());
            myLog()->error('result must be of type array' . $pdo->errorInfo());
            $total = 0;
        }

        if ($request->hasPage()) {
            $limit = $request->getPage()->getSize();
            $offset = $request->getPage()->getIndex();
        } else {
            $limit = 10;
            $offset = 0;
        }
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

        if ($result) {
            foreach ($result as $row) {
                $item = new \Mint\Tulip\V1\SearchResponse\Item;
                $item->setRank($row['rank']);
                $item->setHighlight($row['highlight']);
                $item->setBook($row['book']);
                $item->setParagraph($row['paragraph']);
                $item->setContent($row['content']);
                $output[] = $item;
            }
        }

        console('debug', "total={$total}");
        myLog()->info("total={$total}");
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
        console('debug', "book list: request words = " . implode(',', $keyWords));
        /**
         * 查询业务逻辑
         */
        $pdo = new PdoHelper;
        $pdo->connectDb();

        $bookId = [];
        if ($request->getBooks()->count() > 0) {
            foreach ($request->getBooks()->getIterator() as $book) {
                $bookId[] = $book;
            }
            $queryBookId = ' AND pcd_book_id in (' . implode(',', $bookId) . ') ';
        } else {
            $queryBookId = '';
        }
        console('debug', 'query books = ' . implode(',', $bookId));

        $matchMode = $request->getMatchMode();
        console('debug', 'query mode = ' . $matchMode);
        $queryWhere = $this->makeQueryWhere($keyWords, $matchMode);
        $query = "SELECT pcd_book_id, count(*) as co FROM fts_texts WHERE {$queryWhere['query']} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
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

        console('debug', "total=" . count($output));
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

$port = Config['port'];

if (!isset($port)) {
    console('debug', 'parameter port is required. ');
    return;
}
$server = new \Grpc\RpcServer();
$server->addHttp2Port('0.0.0.0:' . $port);
$server->handle(new Greeter());
console('debug', 'Listening on port :' . $port);
myLog()->debug('Listening on port :' . $port);
$server->run();
