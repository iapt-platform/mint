<?php
//查询term字典

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../group/function.php';

if(isset($_GET["res_id"])){
    PDO_Connect(_FILE_DB_USER_SHARE_,_DB_USERNAME_, _DB_PASSWORD_);
    $id=$_GET["res_id"];
    $type=$_GET["res_type"];
    $query = "SELECT * FROM "._TABLE_USER_SHARE_."  WHERE res_id = ? and res_type=? ";
	$Fetch = PDO_FetchAll($query,array($id,$type));

	#获取协作者名字
	if(count($Fetch)>0){
		$user_info = new UserInfo();
		$group_info = new GroupInfo();
		foreach ($Fetch as $key => $value) {
			# code...
			switch ($value["cooperator_type"]) {
				case 0:
					$Fetch[$key]["user"] = $user_info->getName($value["cooperator_id"]);
					break;
				case 1:
					# 小组协作者
					$Fetch[$key]["user"] = $group_info->getName($value["cooperator_id"]);
					$Fetch[$key]["parent_name"] = $group_info->getName($group_info->getParentId($value["cooperator_id"]));
					break;
				default:
					# 未知类型
					$Fetch[$key]["user"] ="unkow";
					break;
			}
		}
	}
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}
else{
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);
}
?>