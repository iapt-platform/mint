<?php
/*
获取我的文档 文件列表
 */
include "../config.php";
include "../public/_pdo.php";
include "../public/function.php";

if (isset($_GET["keyword"])) {
    $keyword = $_GET["keyword"];
} else {
    $keyword = "";
}
if (isset($_GET["status"])) {
    $status = $_GET["status"];
} else {
    $status = "all";
}
if (isset($_GET["currLanguage"])) {
    $currLanguage = $_GET["currLanguage"];
}

if (isset($_GET["orderby"])) {
    $order_by = $_GET["orderby"];
} else {
    $order_by = "accese_time";
}
if (isset($_GET["order"])) {
    $order = $_GET["order"];
} else {
    $order = "desc";
}

if ($_COOKIE["uid"]) {
    $uid = $_COOKIE["uid"];
} else {
    echo "尚未登录";
    exit;
}

PDO_Connect( _FILE_DB_FILEINDEX_);

switch ($order_by) {
    case "accese_time":
    case "create_time":
    case "modify_time":
        $time_show = $order_by;
        break;
    default:
        $time_show = "accese_time";
        break;
}

switch ($status) {
    case "all":
        $query = "SELECT * from "._TABLE_FILEINDEX_." where user_id='$uid' AND title like '%$keyword%' and status>0 order by $order_by $order";
        break;
    case "share":
        $query = "SELECT * from "._TABLE_FILEINDEX_." where user_id='$uid' AND  title like '%$keyword%' and status>0 and share=1 order by $order_by $order";
        break;
    case "recycle":
        $query = "SELECT * from "._TABLE_FILEINDEX_." where user_id='$uid' AND  title like '%$keyword%' and status=0 order by $order_by $order";
        break;
}
$Fetch = PDO_FetchAll($query);
for ($i = 0; $i < count($Fetch); $i++) {
    $Fetch[$i]["path"] = _get_para_path($Fetch[$i]["book"], $Fetch[$i]["paragraph"]);
}
echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
