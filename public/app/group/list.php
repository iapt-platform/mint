<?php
//查询group 列表

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

//列出 我j参与的群组
PDO_Connect("" . _FILE_DB_GROUP_);
$query = "SELECT group_name,group_id,power FROM group_member  WHERE level = 0 and user_id=?";
$Fetch = PDO_FetchAll($query, array($_COOKIE["userid"]));
foreach ($Fetch as $key => $value) {
	# code...
	$query = "SELECT name FROM group_info  WHERE id=?";
	$groupInfo = PDO_FetchRow($query, array($value["group_id"]));
	if($groupInfo){
		$Fetch[$key]["group_name"]=$groupInfo["name"];
	}
	else{
		$Fetch[$key]["group_name"]="";
	}
}
echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
