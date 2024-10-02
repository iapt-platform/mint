<?php
require_once '../config.php';
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once '../share/function.php';
require_once '../channal/function.php';
require_once '../ucenter/function.php';
require_once '../redis/function.php';

$redis = redis_connect();

$output["status"] = 0;
$output["error"] = "";
$output["data"] = "";
if (!isset($_COOKIE["userid"])) {
    $output["status"] = 1;
    $output["error"] = "#not_login";
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}

$_book = $_POST["book"];
$_para = json_decode($_POST["para"]);
$output["para"] = $_POST["para"];
$output["book"] = $_POST["book"];

/*  创建一个填充了和params相同数量占位符的字符串 */
$place_holders = implode(',', array_fill(0, count($_para), '?'));
$params = $_para;
$params[] = $_book;

#查重复
$channelList = array();
#先查自己的
PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
$query = "SELECT uid FROM "._TABLE_CHANNEL_." WHERE owner_uid = ? and status>0 LIMIT 100";
$FetchChannal = PDO_FetchAll($query, array($_COOKIE["user_uid"]));

foreach ($FetchChannal as $key => $value) {
	# code...
	$channelList[$value["uid"]]=array("power"=>30,"type"=>"my");
}

# 找协作的
$coop_channal =  share_res_list_get($_COOKIE["user_uid"],2);
foreach ($coop_channal as $key => $value) {
	# return res_id,res_type,power res_title  res_owner_id
	if(isset($channelList[$value["res_id"]])){
		if($channelList[$value["res_id"]]<(int)$value["power"]){
			$channelList[$value["res_id"]]=array("power"=>(int)$value["power"]);
		}
	}
	else{
		$channelList[$value["res_id"]]=array("power"=>(int)$value["power"],"type"=>"collaborate");
	}
}

# 查询全网公开 的
PDO_Connect( _FILE_DB_USER_WBW_,_DB_USERNAME_,_DB_PASSWORD_);
$query = "SELECT  channel_uid FROM "._TABLE_USER_WBW_BLOCK_." WHERE  paragraph IN ($place_holders)  AND book_id = ? AND channel_uid IS NOT NULL AND status = 30 group by channel_uid ";
$publicChannel = PDO_FetchAll($query, $params);
foreach ($publicChannel as $key => $channel) {
	# code...
	if(!isset($channelList[$channel["channel_uid"]])){
		$channelList[$channel["channel_uid"]]=array("power"=>10,"type"=>"public");
	}
}

$channelInfo = new Channal($redis);
$userInfo = new UserInfo();
$i = 0;
$outputData = array();


foreach ($channelList as $key => $row) {
    $queryParam = $params;
    $queryParam[] = $key;
    $query = "SELECT count(*) FROM "._TABLE_USER_WBW_BLOCK_." WHERE  paragraph IN ($place_holders)  AND book_id = ? AND channel_uid = ? ";
    $wbwCount = PDO_FetchOne($query, $queryParam);
    $channelList[$key]["wbw_para"] = $wbwCount;
    $channelList[$key]["count"] = count($_para);
	$info = $channelInfo->getChannal($key);
	if($info){
		$channelList[$key]["id"] = $info["uid"];
		$channelList[$key]["name"] = $info["name"];
		$channelList[$key]["lang"] = $info["lang"];
		$channelList[$key]["user"] = $userInfo->getName($info["owner_uid"]);
		$outputData[]=$channelList[$key];
	}
	
}




$output["data"] = $outputData;
echo json_encode($output, JSON_UNESCAPED_UNICODE);
