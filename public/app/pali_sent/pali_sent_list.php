<?php
/*
获取巴利句子列表
输入参数
para: json
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$para = $_POST["para"];

$paraList = json_decode($para);
$output = array();

$dns = _FILE_DB_PALI_SENTENCE_;
$dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$query = "SELECT word_begin as begin,word_end as end ,text FROM "._TABLE_PALI_SENT_." WHERE (book = ?  AND paragraph = ?  ) ";
$stmt = $dbh->prepare($query);
foreach ($paraList as $key => $value) {
    # code...
    $stmt->execute(array($value->book, $value->para));
    $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $sent["info"] = array("book" => $value->book, "para" => $value->para);
    $sent["data"] = $Fetch;
    $output[] = $sent;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
