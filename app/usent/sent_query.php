<?php
/*
get user sentence from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$sent = $_POST["sent"];
$filter = $_POST["filter"];

$sentList = json_decode($sent);
$output = array();

$dns = "sqlite:"._FILE_DB_SENTENCE_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
/* 开始一个事务，关闭自动提交 */

$query="SELECT * FROM sentence WHERE (book = ?  AND paragraph = ? AND begin = ? AND end = ? text <> '' ) order by modify_time DESC limit 0,10";
$stmt = $dbh->prepare($query);
foreach ($sentList as $key => $value) {
    # code...
    $stmt->execute(array($value->book,$value->para,$value->start,$value->end));
    $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
    $sent["info"]=array($value);
    $sent["data"]=$Fetch;
    $sent["length"]=count($Fetch);//句子个数
    $output[] = $sent;
}

    echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>