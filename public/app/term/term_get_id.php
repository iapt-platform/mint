<?php
/*
查询term字典
输入单词列表
输出查到的结果
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';

PDO_Connect( _FILE_DB_TERM_);

$fetch = array();
if (isset($_POST["id"])) {
    $query = "SELECT * FROM "._TABLE_TERM_." WHERE guid = ? ";

    $fetch = PDO_FetchRow($query, array($_POST["id"]));
    if ($fetch) {
        # code...
        $userinfo = new UserInfo();
        $fetch["user"] = $userinfo->getName($fetch["owner"]);
    }
}

echo json_encode($fetch, JSON_UNESCAPED_UNICODE);
