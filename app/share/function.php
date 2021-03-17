<?php 
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../channal/function.php';
require_once '../doc/function.php';
/*
获取某用户的可见的协作资源
$res_type 见readme.md#资源类型 -1全部类型资源
*/
function share_res_list_get($userid,$res_type=-1){
	# 找我加入的群
	$dbhGroup = new PDO(_FILE_DB_GROUP_, "", "");
    $dbhGroup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$query = "SELECT group_id from group_member where user_id = ?  limit 0,100";
	$stmtGroup = $dbhGroup->prepare($query);
	$stmtGroup->execute(array($userid));
	$my_group = $stmtGroup->fetchAll(PDO::FETCH_ASSOC);
	$userList = array();
	$userList[] = $userid;
	foreach ($my_group as $key => $value) {
		# code...
		$userList[]=$value["group_id"];
	}
	
	$place_holders = implode(',', array_fill(0, count($userList), '?'));
	$Fetch=array();
	$PDO = new PDO(_FILE_DB_USER_SHARE_, "", "");
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	if($res_type==-1){
		$query = "SELECT res_id,res_type,power FROM share_cooperator  WHERE is_deleted=0 AND cooperator_id IN ($place_holders) group by res_id ";
		$stmt = $PDO->prepare($query);
		$stmt->execute($userList);
		$Fetch =$stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	else{
		$userList[]=$res_type;
		$query = "SELECT res_id,res_type,power FROM share_cooperator  WHERE is_deleted=0 AND  cooperator_id IN ($place_holders) AND res_type = ? group by res_id";
		$stmt = $PDO->prepare($query);
		$stmt->execute($userList);
		$Fetch =$stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	$channel = new Channal(); 
	foreach ($Fetch as $key => $res) {
		# 获取资源标题 和所有者 
		switch ($res["res_type"]) {
			case 1:
				# pcs 文档
				$Fetch[$key]["res_title"]=pcs_get_title($res["res_id"]);
				break;
			case 2:
				# channel
				$channelInfo = $channel->getChannal($res["res_id"]);
				if($channelInfo){
					$Fetch[$key]["res_title"]=$channelInfo["name"];
					$Fetch[$key]["res_owner_id"]=$channelInfo["owner"];
				}
				else{
					$Fetch[$key]["res_title"]="_unkown_";
					$Fetch[$key]["res_owner_id"]="_unkown_";
				}
				
				break;
			case 3:
				# code...
				break;
			case 4:
				# code...
				break;
			case 5:
				# code...
				break;
																		
			default:
				# code...
				break;
		}
	}

	return $Fetch;

}

//对某个共享资源的权限
function share_get_res_power($userid,$res_id){
		# 找我加入的群
		$dbhGroup = new PDO(_FILE_DB_GROUP_, "", "");
		$dbhGroup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT group_id from group_member where user_id = ?  limit 0,100";
		$stmtGroup = $dbhGroup->prepare($query);
		$stmtGroup->execute(array($userid));
		$my_group = $stmtGroup->fetchAll(PDO::FETCH_ASSOC);
		$userList = array();
		$userList[] = $userid;
		foreach ($my_group as $key => $value) {
			# code...
			$userList[]=$value["group_id"];
		}
		
		$place_holders = implode(',', array_fill(0, count($userList), '?'));
		$Fetch=array();
		$PDO = new PDO(_FILE_DB_USER_SHARE_, "", "");
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

			$userList[]=$res_id;
			$query = "SELECT power FROM share_cooperator  WHERE is_deleted=0 AND  cooperator_id IN ($place_holders) AND res_id = ? ";
			$stmt = $PDO->prepare($query);
			$stmt->execute($userList);
			$Fetch =$stmt->fetchAll(PDO::FETCH_ASSOC);
		$power=0;
		foreach ($Fetch as $key => $value) {
			# code...
			if((int)$value["power"]>$power){
				$power = $value["power"];
			}
		}
		return $power;
}
?>