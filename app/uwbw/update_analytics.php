<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';

global $PDO;
PDO_Connect("" . _FILE_DB_USER_WBW_);

echo "Day Index,创建,更新\n";
$end = strtotime("now") * 1000;
$begin = strtotime("-1 day") * 1000;
for ($i = 0; $i < 30; $i++) {
    //$query = "select count(*) from wbw where \"create_time\" > ".$PDO->quote($begin)." AND \"create_time\" < ".$PDO->quote($end). " AND owner = ".$PDO->quote($_COOKIE["userid"]);
    $create = 0; //PDO_FetchOne($query);
    $query = "select count(*) from wbw where  \"modify_time\" > " . $PDO->quote($begin) . " AND \"modify_time\" < " . $PDO->quote($end) . " AND owner = " . $PDO->quote($_COOKIE["username"]);
    $modify = PDO_FetchOne($query);
    echo date("m/d/Y", $begin / 1000) . ',' . $create . "," . $modify . "\n";
    $end = $begin;
    $begin = strtotime("-1 day", $end / 1000) * 1000;
}
