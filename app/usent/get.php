<?php
/*
get xml doc from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$book = $_GET["book"];
$para = $_GET["para"];
$begin = $_GET["begin"];
$end = $_GET["end"];

$dns = "sqlite:"._FILE_DB_SENTENCE_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  
/* 开始一个事务，关闭自动提交 */

$query="SELECT * FROM sentence WHERE (book = ?  AND paragraph = ? AND begin = ? AND end = ? and text <> '' ) order by modify_time DESC  ";
$stmt = $dbh->prepare($query);
$stmt->execute(array($book,$para,$begin,$end));
$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

?>