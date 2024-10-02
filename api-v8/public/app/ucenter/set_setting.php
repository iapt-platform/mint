<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect(_FILE_DB_USERINFO_);

$query = "UPDATE "._TABLE_USER_INFO_." SET  setting = ? where  userid = ?  ";
$sth = $PDO->prepare($query);

$sth->execute(array($_POST["data"], $_COOKIE["userid"]));
$respond = array("status" => 0, "message" => "");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    $respond['status'] = 1;
    $respond['message'] = $error[2];
} else {
    $respond['status'] = 0;
    $respond['message'] = "设置保存成功";
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
