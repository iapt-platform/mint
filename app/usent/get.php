<?php
/*
get xml doc from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";



$dns = "sqlite:"._FILE_DB_SENTENCE_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
/* 开始一个事务，关闭自动提交 */
$query="SELECT * FROM sentence WHERE 0";
if(isset($_GET["sentences"])){
    $arrSent = explode(",",$_GET["sentences"]);
    /*  创建一个填充了和params相同数量占位符的字符串 */
    $place_holders = implode(',', array_fill(0, count($arrSent), '?'));
    $query="SELECT * FROM sentence WHERE id IN ($place_holders)";
    $stmt = $dbh->prepare($query);
    $stmt->execute($arrSent);
}
else{
    $book = $_GET["book"];
    $para = $_GET["para"];
    $begin = $_GET["begin"];
    $end = $_GET["end"];
    $query="SELECT * FROM sentence WHERE (book = ?  AND paragraph = ? AND begin = ? AND end = ? and text <> '' ) order by modify_time DESC  ";
    $stmt = $dbh->prepare($query);
    $stmt->execute(array($book,$para,$begin,$end));
}

$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

?>