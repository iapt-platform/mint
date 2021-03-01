<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond = array("status" => 0, "message" => "");
if (isset($_COOKIE["userid"]) && isset($_POST["groupid"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    #TODO 先查是否有加人权限
    $query = "SELECT power from group_member where user_id=? and group_id=? ";
    $power = PDO_FetchRow($query, array($_COOKIE["userid"], $_POST["groupid"]));
    if ($power) {
        if ($power["power"] > 1) {
            $respond['status'] = 1;
            $respond['message'] = "no power to add memeber";
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    $query = "SELECT * from group_info where id=?";
    $fc = PDO_FetchRow($query, array($_POST["groupid"]));
    if ($fc) {
        if ($fc["parent"] == 0) {
            $level = 0;
        } else {
            $level = 1;
            #子小组要插入两条记录 第一条插入父层级
            $query = "SELECT * from group_info where id=?";
            $g_parent = PDO_FetchRow($query, array($fc["id"]));
            $query = "INSERT INTO group_member (  user_id  , group_id  , power , group_name , level ,  status )  VALUES  (  ? , ? , ? , ? , ?  ,? ) ";
            $sth = $PDO->prepare($query);
            $sth->execute(array($_POST["userid"], $fc["parent"], 2, $$g_parent["name"], 0, 1));
            $respond = array("status" => 0, "message" => "");
            if (!$sth || ($sth && $sth->errorCode() != 0)) {
                $error = PDO_ErrorInfo();
                $respond['status'] = 1;
                $respond['message'] = $error[2];
            }
        }
    }
    #查询这个
    $query = "SELECT * from group_info where id=?";
    $g_curr = PDO_FetchRow($query, array($_POST["groupid"]));

    $query = "INSERT INTO group_member (  user_id  , group_id  , power , group_name , level ,  status )
		VALUES  (  ? , ? , ? , ? , ?  ,? ) ";
    $sth = $PDO->prepare($query);
    $sth->execute(array($_POST["userid"], $_POST["groupid"], 2, $g_curr["name"], $level, 1));
    $respond = array("status" => 0, "message" => "");
    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2];
    }
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
