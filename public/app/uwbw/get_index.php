<?php

/*
输出某用户 wbw记录列表
*/
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect(_FILE_DB_USER_WBW_);

$query = "SELECT * from "._TABLE_USER_WBW_BLOCK_." where  owner = ? order by modify_time DESC";

$result = PDO_FetchAll($query,array($_COOKIE["userid"]));

echo json_encode($result, JSON_UNESCAPED_UNICODE);
