<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';
require_once '../share/function.php';

if(!isset($_COOKIE["user_uid"])){
	echo json_encode(array(), JSON_UNESCAPED_UNICODE);
	exit;
}

/*
# 找我加入的群
PDO_Connect(""._FILE_DB_GROUP_);
$query = "SELECT group_id from group_member where user_id = ?  limit 0,100";
$my_group = PDO_FetchAll($query,array($_COOKIE["user_uid"]));
$userList = array();
$userList[] = $_COOKIE["user_uid"];
foreach ($my_group as $key => $value) {
	# code...
	$userList[]=$value["group_id"];
}
*/
#查重复
$channelList = array();

//找自己的
PDO_Connect(_FILE_DB_CHANNAL_);
$query = "SELECT id,owner,name,status,lang FROM channal WHERE owner = ?  LIMIT 0,100";
$Fetch_my = PDO_FetchAll($query,array($_COOKIE["user_uid"]));

foreach ($Fetch_my as $key => $value) {
	# code...
	$channelList[$value["id"]]=array("id"=>$value["id"],"owner"=>$value["owner"],"name"=>$value["name"],"power"=>30,"status"=>$value["status"],"lang"=>$value["lang"]);
}

# 找协作的
$coop_channal =  share_res_list_get($_COOKIE["user_uid"],2);
foreach ($coop_channal as $key => $value) {
	# return res_id,res_type,power res_title  res_owner_id
	if(isset($channelList[$value["res_id"]])){
		if($channelList[$value["res_id"]]["power"]<(int)$value["power"]){
			$channelList[$value["res_id"]]["power"]=(int)$value["power"];
		}
	}
	else{
		$channelList[$value["res_id"]]=array("id"=>$value["res_id"],"owner"=>$value["res_owner_id"],"name"=>$value["res_title"],"power"=>(int)$value["power"],"status"=>(int)$value["status"],"lang"=>(int)$value["lang"]);
	}
}

$_userinfo = new UserInfo();

$output = array();
foreach ($channelList as $key => $value) {
    # code...
	$new = $value;
	$name = $_userinfo->getName($value["owner"]);	
	$new["username"] = $name["username"];
	$new["nickname"] = $name["nickname"];	
	$new["count"] = 0;
    $new["all"] = 1;
    $output[]=$new;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>