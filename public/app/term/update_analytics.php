<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../public/function.php';

global $PDO;
PDO_Connect(_FILE_DB_TERM_);

echo "Day Index,创建,更新\n";
$end = strtotime("now");
$begin = strtotime("-1 day");
for ($i = 0; $i < 100; $i++) {
    $query = "SELECT count(*) from "._TABLE_TERM_." where \"create_time\" > " . $PDO->quote($begin) . " AND \"create_time\" < " . $PDO->quote($end);
    $create = PDO_FetchOne($query);
    $query = "SELECT count(*) from "._TABLE_TERM_." where modify_time <> create_time AND \"modify_time\" > " . $PDO->quote($begin) . " AND \"modify_time\" < " . $PDO->quote($end);
    $modify = PDO_FetchOne($query);
    echo date("m/d/Y", $begin) . ',' . $create . "," . $modify . "\n";
    $end = $begin;
    $begin = strtotime("-1 day", $end);
}
