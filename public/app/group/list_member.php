<?php
//查询group member 列表

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

$output = array();
if (isset($_GET["id"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    $id = $_GET["id"];
    $query = "SELECT user_id FROM "._TABLE_GROUP_MEMBER_."  WHERE group_id = ? ";
    $output = PDO_FetchAll($query, array($id));
    $userinfo = new UserInfo();
    foreach ($output as $key => $value) {
        # code...
        $user = $userinfo->getName($value["user_id"]);
        $output[$key]["user_info"] = $user;
    }
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
