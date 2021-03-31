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
	$query = "SELECT group_id from group_member where user_id = ?  limit 0,200";
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
		#所有类型资源
		$query = "SELECT res_id,res_type,power FROM share_cooperator  WHERE is_deleted=0 AND cooperator_id IN ($place_holders) ";
		$stmt = $PDO->prepare($query);
		$stmt->execute($userList);
		$Fetch =$stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	else{
		#指定类型资源
		$userList[]=$res_type;
		$query = "SELECT res_id,res_type,power FROM share_cooperator  WHERE is_deleted=0 AND  cooperator_id IN ($place_holders) AND res_type = ?";
		$stmt = $PDO->prepare($query);
		$stmt->execute($userList);
		$Fetch =$stmt->fetchAll(PDO::FETCH_ASSOC);
	}

	$resOutput = array();
	foreach ($Fetch as $key => $value) {
		# 查重
		if(isset($resOutput[$value["res_id"]])){
			if($value["power"]>$resOutput[$value["res_id"]]["power"]){
				$resOutput[$value["res_id"]]["power"] = $value["power"];
			}
		}
		else{
			$resOutput[$value["res_id"]]= array("power"=> $value["power"],"type" => $value["res_type"]);
		}
	}
	$resList=array();
	foreach ($resOutput as $key => $value) {
		# code...
		$resList[]=array("res_id"=>$key,"res_type"=>(int)$value["type"],"power"=>(int)$value["power"]);
	}
	$channel = new Channal(); 
	foreach ($resList as $key => $res) {
		# 获取资源标题 和所有者 
		switch ($res["res_type"]) {
			case 1:
				# pcs 文档
				$resList[$key]["res_title"]=pcs_get_title($res["res_id"]);
				break;
			case 2:
				# channel
				$channelInfo = $channel->getChannal($res["res_id"]);
				if($channelInfo){
					$resList[$key]["res_title"]=$channelInfo["name"];
					$resList[$key]["res_owner_id"]=$channelInfo["owner"];
					$resList[$key]["status"]=$channelInfo["status"];
					$resList[$key]["lang"]=$channelInfo["lang"];
				}
				else{
					$resList[$key]["res_title"]="_unkown_";
					$resList[$key]["res_owner_id"]="_unkown_";
					$resList[$key]["status"]="0";
					$resList[$key]["lang"]="unkow";
				}
				break;
			case 3:
				# 3 Article 文章
				break;
			case 4:
				# 4 Collection 文集
				break;
			case 5:
				# code...
				break;
																		
			default:
				# code...
				break;
		}
	}

	return $resList;

}

//获取对某个共享资源的权限
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