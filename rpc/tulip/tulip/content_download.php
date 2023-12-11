<?php
require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/console.php';
require dirname(__FILE__) . '/pdo.php';

console('debug', 'download full test search content start');
$param = getopt('b:f:t:');

if (isset($param['b'])) {
    $bookId = (int)$param['b'];
    console('debug', 'update book=' . $bookId);
}

$PDO = new PdoHelper;

$PDO->connectDb();
console('debug', 'connect database finished');

$client = new GuzzleHttp\Client();

$pageSize = 1000;

$urlBase = Config['api_server'] . '/v2/pali-search-data';
console('debug', 'url=' . $urlBase);
if (isset($bookId)) {
    $from = $bookId;
    $to = $bookId;
} else {
    $from = 1;
    $to = 217;
}
if(isset($param['f'])){
    $from = $param['f'];
}
if(isset($param['t'])){
    $to = $param['t'];
}
for ($book = $from; $book <= $to; $book++) {
    $currPage = 1;
    $urlBook = $urlBase . "?book={$book}";
    console('debug', 'fetch book=' . $book);
    do {
        $goNext = false;
        $url = $urlBook . "&start={$currPage}&page_size={$pageSize}";
        console('debug', 'url=' . $url);
        $res = $client->request('GET', $url);
        $status = $res->getStatusCode();
        if ($status === 200) {
            $json = json_decode($res->getBody());
            if ($json->ok) {
                $content = $json->data->rows;
                foreach ($json->data->rows as $row) {
                    $book = $row->book;
                    $paragraph = $row->paragraph;
                    console('debug', "update start book={$book} para={$paragraph} ");
                    $now = date("Y-m-d H:i:s");
                    //查询是否存在
                    $query = 'SELECT id from fts_texts where book=? and paragraph = ?';
                    $result = $PDO->dbSelect($query, [$book, $paragraph]);
                    if (count($result) > 0) {
                        //存在 update
                        $query = 'UPDATE fts_texts set 
                                                "bold_single"=?,
                                                "bold_double"=?,
                                                "bold_multiple"=?,
                                                "content"=?,
                                                "pcd_book_id"=?,
                                                "updated_at"=?  where id=? ';
                        $update = $PDO->dbSelect($query, [
                            $row->bold1,
                            $row->bold2,
                            $row->bold3,
                            $row->content,
                            $row->pcd_book_id,
                            $now,
                            $result[0]['id']
                        ]);
                    } else {
                        // new
                        $query = "INSERT INTO fts_texts (
                                        book,
                                        paragraph,
                                        bold_single,
                                        bold_double,
                                        bold_multiple,
                                        \"content\",
                                        created_at,
                                        updated_at,
                                        pcd_book_id) VALUES
                                            (?,?,?,?,?,?,?,?,? )";
                        $insert = $PDO->dbSelect(
                            $query,
                            [
                                $row->book,
                                $row->paragraph,
                                $row->bold1,
                                $row->bold2,
                                $row->bold3,
                                $row->content,
                                $now,
                                $now,
                                $row->pcd_book_id,
                            ]
                        );
                    }
                }
                console('debug', "update done book={$book} para={$paragraph} ");
                $maxPage = $json->data->count;
                console('debug', 'max page =' . $maxPage);
                if ($currPage + $pageSize < $maxPage) {
                    $goNext = true;
                } else {
                    console('debug', "book {$book} is done");
                    $goNext = false;
                }
            } else {
                console('error', '');
            }
        } else {
            console('error', 'status=' . $status);
        }
        $currPage += $pageSize;
    } while ($goNext);
}

console('debug', 'all done');
