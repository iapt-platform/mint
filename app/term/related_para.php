<?php
/*
查询相关联的书
mula->attakhata->tika
 */
require_once "../public/_pdo.php";
require_once "../path.php";

$result["error"] = "";
$result["data"] = array();

if (isset($_GET["book"])) {
    $book = $_GET["book"];
} else {
    $result["error"] = "no param:book";
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}
if (isset($_GET["para"])) {
    $para = $_GET["para"];
} else {
    $result["error"] = "no param :para";
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    exit;
}

PDO_Connect("" . _FILE_DB_PAGE_INDEX_);
$query = "SELECT bookid,cspara ,book_name FROM cs6_para where book = ? and para= ? ";
$fetch = PDO_FetchAll($query, array($book, $para));
if (count($fetch) > 0) {
    $aBookid = array();
    $place_holders = implode(',', array_fill(0, count($fetch), '?'));
    $query = "SELECT book, para,bookid from cs6_para where book_name = ? and cspara in  ($place_holders)  ";
    $param[] = $fetch[0]["book_name"];

    foreach ($fetch as $key => $value) {
        $param[] = $value["cspara"];
    }
    $fetchAllPara = PDO_FetchAll($query, $param);
    foreach ($fetchAllPara as $bookid) {
        $aBookid["{$bookid["bookid"]}"] = 1;
    }
    $result["data"] = $fetchAllPara;
    $result["curr_book_id"] = $fetch[0]["bookid"];

    //获取书名 列表
    $book_list = array();
    $db_file = _FILE_DB_PALITEXT_;
    PDO_Connect("$db_file");

    foreach ($aBookid as $bookkey => $bookvalue) {
        # code...
        $query = "select * from books where id=" . $bookkey;
        $book_list[] = PDO_FetchRow($query);
    }
    $result["book_list"] = $book_list;

}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
