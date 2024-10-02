<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond = array("status" => 0, "message" => "");
if (isset($_COOKIE["userid"]) && isset($_POST["groupid"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    #TODO 先查是否有删除权限
    $query = "SELECT id from "._TABLE_GROUP_INFO_." where uid=? and owner=? ";
    $gInfo = PDO_FetchRow($query, array($_POST["groupid"], $_COOKIE["userid"]));
    if ($gInfo) {
        #删除group info
        $query = "DELETE from "._TABLE_GROUP_INFO_." where uid=? and owner=? ";
        PDO_Execute($query, array($_POST["groupid"], $_COOKIE["userid"]));
        #删除 组员
        $query = "DELETE from "._TABLE_GROUP_MEMBER_." where group_id=? ";
        PDO_Execute($query, array($_POST["groupid"]));
        #删除到此组的分享
        $query = "DELETE from "._TABLE_USER_SHARE_." where cooperator_id=? and cooperator_type=1 ";
        PDO_Execute($query, array($_POST["groupid"]));

    } else {
        $respond['status'] = 1;
        $respond['message'] = "no power to delete ";
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
