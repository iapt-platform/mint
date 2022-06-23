<?php
/*
 *查询相关联的书
 *mula->attakhata->tika
 *算法：
 *在原始的html 文件里 如 s0404m1.mul.htm 有 <a name="para2_an8"></a>
 * 在 so404a.att.htm 里也有 </a><a name="para2_an8"></a>
 * 这说明这两个段落是关联段落，para2是段落编号 an8是书名只要书名一样，段落编号一样。
 * 两个就是关联段落
 * 
 * 表名：cs6_para
 * 所以数据库结构是
 * book 书号 1-217
 * para 段落号
 * bookid
 * cspara 上述段落号
 * book_name 上述书名
 * 
 * 输入 book para
 * 查询书名和段落号
 * 输入这个书名和段落号
 * 查询有多少段落有一样的书名和段落号
 * 有些book 里面有两本书。所以又加了一个bookid 
 * 每个bookid代表一本真正的书。所以bookid 要比 book 多
 * bookid 是为了输出书名用的。不是为了查询相关段落
 * 
 * 数据要求：
 * 制作时包含全部段落。做好后把没有相关段落的段落删掉？？
 * 
 */
require_once "../public/_pdo.php";
require_once "../config.php";

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

PDO_Connect(_FILE_DB_PAGE_INDEX_);
$query = "SELECT bookid,cspara ,book_name FROM cs6_para where book = ? and para= ? and cspara > 0";
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
    PDO_Connect(_FILE_DB_PALITEXT_);

    foreach ($aBookid as $bookkey => $bookvalue) {
        # code...
        $query = "SELECT * from "._TABLE_PALI_BOOK_NAME_." where id = ? ";
        $book_list[] = PDO_FetchRow($query,array($bookkey));
    }
    $result["book_list"] = $book_list;

}

echo json_encode($result, JSON_UNESCAPED_UNICODE);
