<?php

/*
输出某用户 wbw记录列表
*/
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect(_FILE_DB_USER_WBW_,_DB_USERNAME_,_DB_PASSWORD_);

$query = "SELECT uid , channel_uid,book_id,paragraph,lang,status,updated_at from "._TABLE_USER_WBW_BLOCK_." where  creator_uid = ? order by updated_at DESC";

$result = PDO_FetchAll($query,array($_COOKIE["userid"]));

echo json_encode($result, JSON_UNESCAPED_UNICODE);
