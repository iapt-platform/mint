<?php
/*
get user sentence from db
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../ucenter/function.php";

$sent = $_POST["sent"];
$filter = $_POST["filter"];

$sentList = json_decode($sent);
$output = array();

$dns = "" . _FILE_DB_SENTENCE_;
$dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
/* 开始一个事务，关闭自动提交 */

$query = "SELECT * FROM sentence WHERE (book = ?  AND paragraph = ? AND begin = ? AND end = ? AND strlen > 0  ) order by modify_time DESC limit 0,10";
$stmt = $dbh->prepare($query);
foreach ($sentList as $key => $value) {
    # code...
    $stmt->execute(array($value->book, $value->para, $value->start, $value->end));
    $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
    for ($i = 0; $i < count($Fetch); $i++) {
        $Fetch[$i]["nickname"] = ucenter_getA($Fetch[$i]["editor"]);
    }
    $sent = array();
    $sent["info"] = $value;
    $sent["data"] = $Fetch;
    $sent["count"] = count($Fetch); //句子个数
    $output[] = $sent;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
