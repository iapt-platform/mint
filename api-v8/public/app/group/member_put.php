<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();


$respond = array("status" => 0, "message" => "");

set_exception_handler(function($e){
    $respond['status'] = 1;
    $respond['message'] = $e->getFile().$e->getLine().$e->getMessage();
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
});
if (isset($_COOKIE["userid"]) && isset($_POST["groupid"])) {
    PDO_Connect("" . _FILE_DB_GROUP_);
    #先查是否有加人权限 0:拥有者，1.管理员
    $query = "SELECT power from "._TABLE_GROUP_MEMBER_." where user_id=? and group_id=? ";
    $power = PDO_FetchRow($query, array($_COOKIE["userid"], $_POST["groupid"]));
    if (!$power || $power["power"] > 1) {
        $respond['status'] = 1;
        $respond['message'] = "no power to add memeber";
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }

    #查询组信息
    $query = "SELECT * from "._TABLE_GROUP_INFO_." where uid=?";
    $g_curr = PDO_FetchRow($query, array($_POST["groupid"]));
    if (!$g_curr) {
        $respond['status'] = 1;
        $respond['message'] = '组不存在';
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }
#查询是否有重复记录
    $query = "SELECT * from "._TABLE_GROUP_MEMBER_." where user_id=? and group_id=?";
    $isExist = PDO_FetchRow($query, array($_POST["userid"], $_POST["groupid"]));
    if ($isExist) {
        $respond['status'] = 1;
        $respond['message'] = '组员已经存在';
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }

    $query = "INSERT INTO "._TABLE_GROUP_MEMBER_." (id,  user_id  , group_id  , power , group_name , level ,  status )
		VALUES  (  ? , ? , ? , ? , ?  ,? ,?) ";
    $sth = $PDO->prepare($query);
    $sth->execute(array($snowflake->id(),$_POST["userid"], $_POST["groupid"], 2, $g_curr["name"], 0, 1));
    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2];
        echo json_encode($respond, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
