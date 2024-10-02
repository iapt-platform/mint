<?php 
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../channal/function.php';
require_once '../article/function.php';
require_once '../collect/function.php';
require_once '../redis/function.php';
require_once '../doc/function.php';
/*
获取某用户的可见的协作资源
$res_type 见readme.md#资源类型 -1全部类型资源
*/
function share_res_list_get($userid,$res_type=-1){
	$redis = redis_connect();
	# 找我加入的群
	$dbhGroup = new PDO(_FILE_DB_GROUP_, _DB_USERNAME_, _DB_PASSWORD_);
    $dbhGroup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$query = "SELECT group_id from "._TABLE_GROUP_MEMBER_." where user_id = ?  limit 500";
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
	$PDO = new PDO(_FILE_DB_USER_SHARE_, _DB_USERNAME_, _DB_PASSWORD_);
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	if($res_type==-1){
		#所有类型资源
		$query = "SELECT res_id,res_type,power FROM "._TABLE_USER_SHARE_."  WHERE  cooperator_id IN ($place_holders) ";
		$stmt = $PDO->prepare($query);
		$stmt->execute($userList);
		$Fetch =$stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	else{
		#指定类型资源
		$userList[]=$res_type;
		$query = "SELECT res_id,res_type,power FROM "._TABLE_USER_SHARE_."  WHERE   cooperator_id IN ($place_holders) AND res_type = ?";
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
	$article = new Article($redis); 
	$collection = new CollectInfo($redis); 
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
					$resList[$key]["res_owner_id"]=$channelInfo["owner_uid"];
					$resList[$key]["type"]=$channelInfo["type"];
					$resList[$key]["status"]=$channelInfo["status"];
					$resList[$key]["lang"]=$channelInfo["lang"];
				}
				else{
					$resList[$key]["res_title"]="_unkown_";
					$resList[$key]["res_owner_id"]="_unkown_";
					$resList[$key]["type"]=$channelInfo["type"];
					$resList[$key]["status"]="0";
					$resList[$key]["lang"]="unkow";
				}
				break;
			case 3:
				# 3 Article 文章
				$aInfo = $article->getInfo($res["res_id"]);
				if($aInfo){
					$resList[$key]["res_title"]=$aInfo["title"];
					$resList[$key]["res_owner_id"]=$aInfo["owner"];
					$resList[$key]["status"]=$aInfo["status"];
					$resList[$key]["lang"]='';
				}
				else{
					$resList[$key]["res_title"]="_unkown_";
					$resList[$key]["res_owner_id"]="_unkown_";
					$resList[$key]["status"]="0";
					$resList[$key]["lang"]="unkow";
				}
				break;
			case 4:
				# 4 Collection 文集
				$aInfo = $collection->get($res["res_id"]);
				if($aInfo){
					$resList[$key]["res_title"]=$aInfo["title"];
					$resList[$key]["res_owner_id"]=$aInfo["owner"];
					$resList[$key]["status"]=$aInfo["status"];
					$resList[$key]["lang"]=$aInfo["lang"];
				}
				else{
					$resList[$key]["res_title"]="_unkown_";
					$resList[$key]["res_owner_id"]="_unkown_";
					$resList[$key]["status"]="0";
					$resList[$key]["lang"]="unkow";
				}
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
		if($userid==='0'){
			#未登录用户 没有共享资源
			return 0;
		}
		# 找我加入的群
		$dbhGroup = new PDO(_FILE_DB_GROUP_, _DB_USERNAME_, _DB_PASSWORD_);
		$dbhGroup->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		$query = "SELECT group_id from "._TABLE_GROUP_MEMBER_." where user_id = ?  limit 500";
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
		$PDO = new PDO(_FILE_DB_USER_SHARE_, _DB_USERNAME_, _DB_PASSWORD_);
		$PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

			$userList[]=$res_id;
			$query = "SELECT power FROM "._TABLE_USER_SHARE_."  WHERE  cooperator_id IN ($place_holders) AND res_id = ? ";
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