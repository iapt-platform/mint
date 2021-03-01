<?php
//查询group 列表

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

//没有id 列出 我的群组
PDO_Connect("" . _FILE_DB_GROUP_);
$query = "SELECT group_name,group_id,power FROM group_member  WHERE level = 0 and user_id=?";
$Fetch = PDO_FetchAll($query, array($_COOKIE["userid"]));

echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
