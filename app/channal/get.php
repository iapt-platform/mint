<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';
require_once '../share/function.php';

if(!isset($_COOKIE["userid"])){
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
//找自己的
PDO_Connect(""._FILE_DB_CHANNAL_);
$query = "SELECT id,owner,name FROM channal WHERE owner = ?  LIMIT 0,100";
$Fetch_my = PDO_FetchAll($query,array($_COOKIE["userid"]));


# 找协作的
$coop_channal =  share_res_list_get($_COOKIE["userid"],2);
$Fetch_coop = array();
foreach ($coop_channal as $key => $value) {
	# return res_id,res_type,power res_title  res_owner_id
	$res["id"]=$value["res_id"];
	$res["owner"]=$value["res_owner_id"];
	$res["name"]=$value["res_title"];
	$res["power"]=$value["power"];
	$Fetch_coop[]=$res;
}
/*

$place_holders = implode(',', array_fill(0, count($userList), '?'));

$query = "SELECT channal_id FROM cooperation WHERE  user_id IN ($place_holders) ";
$coop_channal = PDO_FetchAll($query,$userList);
if(count($coop_channal)>0){
    foreach ($coop_channal as $key => $value) {
        # code...
        $channal[]=$value["channal_id"];
    }
    // 创建一个填充了和params相同数量占位符的字符串 
    $place_holders = implode(',', array_fill(0, count($channal), '?'));
    $query = "SELECT * FROM channal WHERE id IN ($place_holders) order by owner";
    $Fetch_coop = PDO_FetchAll($query,$channal);
}
*/
$all = array_merge_recursive($Fetch_my,$Fetch_coop);

$_userinfo = new UserInfo();

$output = array();
foreach ($all as $key => $value) {
    # code...
	$new = $value;
	$name = $_userinfo->getName($value["owner"]);	
	if($value["owner"]===$_COOKIE["userid"]){
		$new["username"] = "_you_";
		$new["nickname"] = "_you_";
		$new["power"] = 30;
	}
	else{
		$new["username"] = $name["username"];
		$new["nickname"] = $name["nickname"];		
		$new["power"] = $value["power"];		
	}

	$new["count"] = 0;
    $new["all"] = 1;
    $output[]=$new;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>