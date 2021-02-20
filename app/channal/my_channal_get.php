<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../group/function.php';


if(isset($_GET["id"])){
    PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
    $id=$_GET["id"];
    $query = "SELECT * FROM channal  WHERE id = ? ";
	$Fetch = PDO_FetchRow($query,array($id));

	#获取协作者
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
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}
else{
    PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
    $query = "SELECT * FROM channal  WHERE owner = ? ";
    $Fetch = PDO_FetchAll($query,array($_COOKIE["userid"]));
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}


?>