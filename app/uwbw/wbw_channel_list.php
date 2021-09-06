<?php
require_once '../path.php';
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once '../share/function.php';
require_once '../channal/function.php';
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

PDO_Connect(_FILE_DB_CHANNAL_);
$query = "SELECT id FROM channal WHERE owner = ?  LIMIT 0,100";
$FetchChannal = PDO_FetchAll($query, array($_COOKIE["userid"]));

foreach ($FetchChannal as $key => $value) {
	# code...
	$channelList[$value["id"]]=array("power"=>30);
}

# 找协作的
$coop_channal =  share_res_list_get($_COOKIE["userid"],2);
foreach ($coop_channal as $key => $value) {
	# return res_id,res_type,power res_title  res_owner_id
	if(isset($channelList[$value["res_id"]])){
		if($channelList[$value["res_id"]]<(int)$value["power"]){
			$channelList[$value["res_id"]]=array("power"=>(int)$value["power"]);
		}
	}
	else{
		$channelList[$value["res_id"]]=array("power"=>(int)$value["power"]);
	}
}

# 查询全网公开 的
PDO_Connect( _FILE_DB_USER_WBW_);
$query = "SELECT  channal FROM "._TABLE_USER_WBW_BLOCK_." WHERE  paragraph IN ($place_holders)  AND book = ? AND channal IS NOT NULL AND status = 30 group by channal ";
$publicChannel = PDO_FetchAll($query, $params);
foreach ($publicChannel as $key => $channel) {
	# code...
	if(!isset($channelList[$channel["channal"]])){
		$channelList[$channel["channal"]]=array("power"=>10);
	}
}

$channelInfo = new Channal($redis);
$i = 0;
$outputData = array();


foreach ($channelList as $key => $row) {
    $queryParam = $params;
    $queryParam[] = $key;
    $query = "SELECT count(*) FROM "._TABLE_USER_WBW_BLOCK_." WHERE  paragraph IN ($place_holders)  AND book = ? AND channal = ? ";
    $wbwCount = PDO_FetchOne($query, $queryParam);
    $channelList[$key]["wbw_para"] = $wbwCount;
    $channelList[$key]["count"] = count($_para);
	$info = $channelInfo->getChannal($key);
    $channelList[$key]["id"] = $info["id"];
    $channelList[$key]["name"] = $info["name"];
    $channelList[$key]["lang"] = $info["lang"];
	$outputData[]=$channelList[$key];
}




$output["data"] = $outputData;
echo json_encode($output, JSON_UNESCAPED_UNICODE);
