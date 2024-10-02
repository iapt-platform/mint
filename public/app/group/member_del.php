<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond = array("status" => 0, "message" => "");
set_exception_handler(function($e){
    $respond['status'] = 1;
    $respond['message'] = $e->getFile().$e->getLine().$e->getMessage();
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
});

if (!isset($_COOKIE["userid"])) {
    $respond['status'] = 1;
    $respond['message'] = "尚未登录";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}
if (isset($_POST["groupid"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    $mypower = 100;
    # 先查是否有删人权限
    #是否是拥有者
    $query = "SELECT * from "._TABLE_GROUP_INFO_." where uid=?";
    $fc = PDO_FetchRow($query, array($_POST["groupid"]));
    if ($fc) {
        if ($fc["owner"] == $_COOKIE["userid"]) {
            $mypower = 0;
        }
    }
    if ($mypower != 0) {
        #非拥有者，看看是不是管理员
        $query = "SELECT power from "._TABLE_GROUP_MEMBER_." where user_id=? and group_id=? ";
        $power = PDO_FetchRow($query, array($_COOKIE["user_uid"], $_POST["groupid"]));
        if ($power) {
            $mypower = (int) $power["power"];
        }
        #普通成员无权移除他人
        if ($mypower > 1) {
            $respond['status'] = 1;
            $respond['message'] = "no power to remove memeber";
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    # 查询被删除人的权限
    $query = "SELECT power from "._TABLE_GROUP_MEMBER_." where user_id=? and group_id=? ";
    $power = PDO_FetchRow($query, array($_POST["userid"], $_POST["groupid"]));
    $userpower = 0;
    if ($power) {
        $userpower = (int) $power["power"];
    }
    #操作人的权限不足
    if ($mypower >= $userpower) {
        $respond['status'] = 1;
        $respond['message'] = "can not removed 权限不足";
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }

    #删除

    $query = "DELETE from "._TABLE_GROUP_MEMBER_." where user_id=? and group_id =? ";
    PDO_Execute($query, array($_POST["userid"], $_POST["groupid"]));

} else {
    $respond['status'] = 1;
    $respond['message'] = "参数不足";
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
