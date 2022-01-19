<?php
/*
统计三十天内的逐词译创建与更新
*/
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';

global $PDO;
PDO_Connect(_FILE_DB_USER_WBW_,_DB_USERNAME_,_DB_PASSWORD_);

echo "Day Index,创建,更新\n";
$end = strtotime("now") * 1000;
$begin = strtotime("-1 day") * 1000;
for ($i = 0; $i < 30; $i++) {
    $create = 0; 
    $query = "SELECT count(*) from "._TABLE_USER_WBW_." where  modify_time > ? AND  modify_time  < ? AND owner = ?" ;
    $modify = PDO_FetchOne($query,array($begin,$end,$_COOKIE["username"]));
    echo date("m/d/Y", $begin / 1000) . ',' . $create . "," . $modify . "\n";
    $end = $begin;
    $begin = strtotime("-1 day", $end / 1000) * 1000;
}
