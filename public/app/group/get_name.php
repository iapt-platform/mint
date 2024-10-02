<?php
//查询group 列表

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

$output = array();
if (isset($_GET["name"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    $query = "SELECT * FROM "._TABLE_GROUP_INFO_."  WHERE name like ?  limit 50";
    $Fetch = PDO_FetchAll($query, array("%" . $_GET["name"] . "%"));

    $user_info = new UserInfo();
    foreach ($Fetch as $key => $value) {
        # code...
        $Fetch[$key]["username"] = $user_info->getName($value["owner"]);
    }
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}
