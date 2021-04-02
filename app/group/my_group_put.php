<?php
#新增群组或项目
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond = array("status" => 0, "message" => "");
if (isset($_COOKIE["userid"])) {
    PDO_Connect(_FILE_DB_GROUP_);
	#先查询是否有重复的组名
	$query = "SELECT id FROM group_info  WHERE name = ? ";
    $Fetch = PDO_FetchRow($query, array($_POST["name"]));
	if ($Fetch) {
		$respond['status'] = 1;
        $respond['message'] = "错误：有相同的组名称,请选择另一个名称。";
		echo json_encode($respond, JSON_UNESCAPED_UNICODE);
		exit;
	}
    $query = "INSERT INTO group_info ( id,  parent  , name  , description ,  status , owner ,create_time )
	                       VALUES  ( ?, ? , ? , ? , ? , ?  ,? ) ";
    $sth = $PDO->prepare($query);
    $newid = UUID::v4();
    $sth->execute(array($newid, $_POST["parent"], $_POST["name"], "", 1, $_COOKIE["userid"], mTime()));
    $respond = array("status" => 0, "message" => "");
    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2];
    }

	#将创建者添加到成员中
    $query = "INSERT INTO group_member (  user_id  , group_id  , power , group_name , level ,  status )
		VALUES  (  ? , ? , ? , ? , ?  ,? ) ";
    $sth = $PDO->prepare($query);
    $sth->execute(array($_COOKIE["userid"], $newid, 0, $_POST["name"], 0, 1));
    $respond = array("status" => 0, "message" => "");
    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status'] = 1;
        $respond['message'] = $error[2];
    }
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
