<?php


require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

function group_get_name($id){
	if(isset($id)){
		PDO_Connect("sqlite:"._FILE_DB_GROUP_);
		$query = "SELECT name FROM group_info  WHERE id=?";
		$Fetch = PDO_FetchRow($query,array($id));
		if($Fetch){
			return $Fetch["name"];
		}
		else{
			return "";
		}
	}
	else{
		return "";
	}
}


?>