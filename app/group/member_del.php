<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond = array("status" => 0, "message" => "");
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
    $query = "SELECT * from group_info where id=?";
    $fc = PDO_FetchRow($query, array($_POST["groupid"]));
    if ($fc) {
        if ($fc["parent"] == 0) {
            if ($fc["owner"] == $_COOKIE["userid"]) {
                $mypower = 0;
            }
        } else {
            $query = "SELECT owner  from group_info where id=?";
            $g_parent = PDO_FetchRow($query, array($fc["parent"]));
            if ($g_parent && $g_parent["owner"] == $_COOKIE["userid"]) {
                $mypower = 0;
            }
        }
    }
    if ($mypower != 0) {
        #非拥有者，看看是不是管理员
        $query = "SELECT power from group_member where user_id=? and group_id=? ";
        $power = PDO_FetchRow($query, array($_COOKIE["userid"], $_POST["groupid"]));
        if ($power) {
            $mypower = (int) $power["power"];
        }
        if ($mypower > 1) {
            $respond['status'] = 1;
            $respond['message'] = "no power to remove memeber";
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    # 查询被删除人的权限
    $query = "SELECT power from group_member where user_id=? and group_id=? ";
    $power = PDO_FetchRow($query, array($_POST["userid"], $_POST["groupid"]));
    $userpower = 0;
    if ($power) {
        $userpower = (int) $power["power"];
    }
    #操作人的权限不足
    if ($mypower >= $userpower) {
        $respond['status'] = 1;
        $respond['message'] = "can not removed";
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $query = "SELECT * from group_info where id=?";
    $fc = PDO_FetchRow($query, array($_POST["groupid"]));
    if ($fc) {
        $idList = array();
        $idList[] = $_POST["userid"];
        $idList[] = $_POST["groupid"];
        if ($fc["parent"] == 0) {
            //group
            $level = 0;
            #查询project
            $query = "SELECT id from group_info where parent=?";
            $g_project = PDO_FetchAll($query, array($_POST["groupid"]));
            foreach ($g_project as $key => $parentid) {
                # code...
                $idList[] = $parentid["id"];
            }
        }
    }
    #删除
    $place_holders = implode(',', array_fill(0, count($idList), '?'));
    $query = "DELETE from group_member where user_id=? and group_id IN ($place_holders)";
    PDO_Execute($query, $idList);

    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2];
    }
} else {
    $respond['status'] = 1;
    $respond['message'] = "参数不足";
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
