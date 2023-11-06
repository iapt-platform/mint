<?php

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';



class Greeter extends \Mint\Tulip\V1\SearchStub
{
    private $_pdo = null;
    private function connectDb(){
        /**
         * 连接数据库
         */
        $db = Config['database']['driver'];
        $db .= ":host=".Config['database']['host'];
        $db .= ";port=".Config['database']['port'];
        $db .= ";dbname=".Config['database']['name'];
        $db .= ";user=".Config['database']['user'];
        $db .= ";password=".Config['database']['password'].";";
        echo 'connect to db host='.Config['database']['host'] . ' name='.Config['database']['name'].PHP_EOL;
        try {
            $PDO = new PDO($db,
                        Config['database']['user'],
                        Config['database']['password'],
                        array(PDO::ATTR_PERSISTENT=>true));
        }catch(PDOException $e) {
            echo 'connect to db fail'.PHP_EOL;
            print $e->getMessage();
            return false;
        }
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $this->_pdo = $PDO;
    }
    private function dbSelect($query, $params=null)
    {
        if($this->_pdo === null){
            return false;
        }
        if (isset($params)) {
            $stmt = $this->_pdo->prepare($query);
            $stmt->execute($params);
        } else {
            $stmt = $this->_pdo->query($query);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

        /**
     * Create a new instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->connectDb();
    }

    public function Pali(
        \Mint\Tulip\V1\SearchRequest $request,
        \Grpc\ServerContext $context
    ): ?\Mint\Tulip\V1\SearchResponse {
        $keyWords = [];
        foreach ($request->getKeywords()->getIterator() as $word) {
            $keyWords[] = $word;
        }
        echo "[".date("Y/m/d h:i:sa") ."] pali search: request words = ".implode(',',$keyWords) .PHP_EOL;

        /**
         * 查询业务逻辑
         */

        $searchChapters = [];
        $searchBooks = [];
        $searchBookId = [];
        $bookId = [];
        if($request->getBooks()->count()>0){
            foreach ($request->getBooks()->getIterator() as $book) {
                $bookId[] = $book;
            }
            $queryBookId = ' AND pcd_book_id in ('.implode(',',$bookId).') ';
        }else{
            $queryBookId = '';
        }
        echo 'query books = '.implode(',',$bookId).PHP_EOL;

        $matchMode = $request->getMatchMode();
        echo 'query mode = '.$matchMode.PHP_EOL;
        $param = [];
        $countParam = [];
        switch ($matchMode) {
            case 'complete':
            case 'case':
                # code...
                $querySelect_rank_base = " ts_rank('{0.1, 1, 0.3, 0.2}',
                                                full_text_search_weighted,
                                                websearch_to_tsquery('pali', ?)) ";
                $querySelect_rank_head = implode('+', 
                                            array_fill(0, count($keyWords), 
                                            $querySelect_rank_base));

                $param = array_merge($param,$keyWords);
                $querySelect_rank = " {$querySelect_rank_head} AS rank, ";
                $querySelect_highlight = " ts_headline('pali', content,
                                            websearch_to_tsquery('pali', ?),
                                            'StartSel = ~~, StopSel = ~~,MaxWords=3500, 
                                            MinWords=3500,HighlightAll=TRUE')
                                            AS highlight,";
                array_push($param,implode(' ',$keyWords));
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
        $_queryWhere = $this->makeQueryWhere($keyWords,$matchMode);
        $queryWhere = $_queryWhere['query'];
        $param = array_merge($param,$_queryWhere['param']);

        $querySelect_2 = "  book,paragraph,content ";

        $queryCount = "SELECT count(*) as co FROM fts_texts WHERE {$queryWhere} {$queryBookId};";
        $resultCount = $this->dbSelect($queryCount, $_queryWhere['param']);
        $total = $resultCount[0]['co'];

        if($request->hasPage()){
            $limit = $request->getPage()->getSize();
            $offset = $request->getPage()->getIndex();
        }else{
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

        $result = $this->dbSelect($query, $param);

         //返回数据
        $response = new \Mint\Tulip\V1\SearchResponse();
        $output = $response->getItems();
        foreach ($result as $row) {
            $item = new \Mint\Tulip\V1\SearchResponse\Item;
            $item->setRank($row['rank']);
            $item->setHighlight($row['highlight']);
            $item->setBook($row['book']);
            $item->setParagraph($row['paragraph']);
            $item->setContent($row['content']);
            $output[] = $item;
        }
        echo "total={$total}".PHP_EOL;
        $response->setTotal($total);
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
        echo "book list: request words = ".implode(',',$keyWords) .PHP_EOL;
        /**
         * 查询业务逻辑
         */

         $searchChapters = [];
         $searchBooks = [];
         $searchBookId = [];
         $bookId = [];
         if($request->getBooks()->count()>0){
             foreach ($request->getBooks()->getIterator() as $book) {
                 $bookId[] = $book;
             }
             $queryBookId = ' AND pcd_book_id in ('.implode(',',$bookId).') ';
         }else{
             $queryBookId = '';
         }
         echo 'query books = '.implode(',',$bookId).PHP_EOL;
 
         $matchMode = $request->getMatchMode();
         echo 'query mode = '.$matchMode.PHP_EOL;
         $queryWhere = $this->makeQueryWhere($keyWords,$matchMode);
         $query = "SELECT pcd_book_id, count(*) as co FROM fts_texts WHERE {$queryWhere['query']} {$queryBookId} GROUP BY pcd_book_id ORDER BY co DESC;";
         $result = $this->dbSelect($query, $queryWhere['param']);
         //返回数据
         $response = new \Mint\Tulip\V1\BookListResponse();
         $output = $response->getItems();
         foreach ($result as $row) {
             $item = new \Mint\Tulip\V1\BookListResponse\Item;
             $item->setBook($row['pcd_book_id']);
             $item->setCount($row['co']);
             $output[] = $item;
         }
         echo "total=".count($output).PHP_EOL;
         return $response;
    }

    private function makeQueryWhere($key,$match){
        $param = [];
        $queryWhere = '';
        switch ($match) {
            case 'complete':
            case 'case':
                # code...
                $queryWhereBase = " full_text_search_weighted @@ websearch_to_tsquery('pali', ?) ";
                $queryWhereBody = implode(' or ', array_fill(0, count($key), 
                                    $queryWhereBase));
                $queryWhere = " ({$queryWhereBody}) ";
                $param = array_merge($param,$key);
                break;
            case 'similar':
                # 形似，去掉变音符号
                $queryWhere = " full_text_search_weighted_unaccent @@ websearch_to_tsquery('pali_unaccent', ?) ";
                $key = $this->getWordEn($key[0]);
                $param = [$key];
                break;
        };
        return (['query'=>$queryWhere,'param'=>$param]);
    }

    private function getWordEn($strIn)
    {
        $out = str_replace(["ā","ī","ū","ṅ","ñ","ṭ","ḍ","ṇ","ḷ","ṃ"],
                        ["a","i","u","n","n","t","d","n","l","m"], $strIn);
        return ($out);
    }
}

$port = Config['port'];

if (!isset($port)) {
    echo 'parameter port is required. ';
    return;
}
$server = new \Grpc\RpcServer();
$server->addHttp2Port('0.0.0.0:' . $port);
$server->handle(new Greeter());
echo 'Listening on port :' . $port . PHP_EOL;
$server->run();