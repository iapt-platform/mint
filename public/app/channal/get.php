<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';
require_once '../share/function.php';

if(!isset($_COOKIE["userid"]) || !isset($_COOKIE["user_uid"])){
	echo json_encode(array(), JSON_UNESCAPED_UNICODE);
	exit;
}

/*
# 找我加入的群
PDO_Connect(""._FILE_DB_GROUP_);
$query = "SELECT group_id from group_member where user_id = ?  limit 0,100";
$my_group = PDO_FetchAll($query,array($_COOKIE["userid"]));
$userList = array();
$userList[] = $_COOKIE["userid"];
foreach ($my_group as $key => $value) {
	# code...
	$userList[]=$value["group_id"];
}
*/
#查重复
$channelList = array();

//找自己的
PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
$query = "SELECT uid,owner_uid,name,status,lang,type FROM "._TABLE_CHANNEL_." WHERE owner_uid = ? order by updated_at DESC LIMIT 200";
$Fetch_my = PDO_FetchAll($query,array($_COOKIE["user_uid"]));

#去掉重复的
foreach ($Fetch_my as $key => $value) {
	# code...
	$channelList[$value["uid"]]=array(
        "uid"=>$value["uid"],
        "owner_uid"=>$value["owner_uid"],
        "name"=>$value["name"],
        "power"=>30,
        "type"=>$value["type"],
        "status"=>$value["status"],
        "lang"=>$value["lang"]
        );
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
		$channelList[$value["res_id"]]=array(
            "uid"=>$value["res_id"],
            "owner_uid"=>$value["res_owner_id"],
            "name"=>$value["res_title"],
            "power"=>(int)$value["power"],
            "type"=>(int)$value["type"],
            "status"=>(int)$value["status"],
            "lang"=>(int)$value["lang"]
            );
	}
}

$_userinfo = new UserInfo();

$output = array();
foreach ($channelList as $key => $value) {
    # code...
	$new = $value;
	$name = $_userinfo->getName($value["owner_uid"]);
	$new["username"] = $name["username"];
	$new["nickname"] = $name["nickname"];
	$new["count"] = 0;
    $new["all"] = 1;
    $new["owner"] = $value["owner_uid"];

    $output[]=$new;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>
