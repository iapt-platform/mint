<?php
//获取我的channel 列表，已经废弃

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../group/function.php';


if(isset($_GET["id"])){
    PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
    $id=$_GET["id"];
    $query = "SELECT uid as id ,owner_uid as owner, name , summary , status , lang , create_time,modify_time  FROM "._TABLE_CHANNEL_."  WHERE uid=? ";
	$Fetch = PDO_FetchRow($query,array($id));

	#TODO获取协作者
	/*
	if($Fetch){
		$user_info = new UserInfo();
		$group_info = new GroupInfo();
		$query = "SELECT * FROM cooperation  WHERE channal_id = ? ";
		$coop = PDO_FetchAll($query,array($id));
		foreach ($coop as $key => $value) {
			# code...
			if($value["type"]==0){
				$coop[$key]["user_name"] = $user_info->getName($value["user_id"]);
			}
			else if($value["type"]==1){
				$coop[$key]["user_name"] = $group_info->getName($value["user_id"]);
			}
		}
		$Fetch["coop"]=$coop;
	}
	*/
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}
else{
    PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
    $query = "SELECT uid as id ,owner_uid as owner, name , summary , status , lang , create_time,modify_time FROM "._TABLE_CHANNEL_."  WHERE owner_uid = ? ";
    $Fetch = PDO_FetchAll($query,array($_COOKIE["user_uid"]));
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}


?>