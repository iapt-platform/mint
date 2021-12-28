<?php
/*
获取句子译文
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../channal/function.php";
require_once "../redis/function.php";
require_once "../ucenter/function.php";
require_once "../share/function.php";

$redis = redis_connect();

#查询有阅读权限的channel
$channal_list = array();
if (isset($_COOKIE["userid"])) {
    PDO_Connect(_FILE_DB_CHANNAL_);
    $query = "SELECT id from channal where owner = ?   limit 0,100";
    $Fetch_my = PDO_FetchAll($query, array($_COOKIE["userid"]));
    foreach ($Fetch_my as $key => $value) {
        # code...
        $channal_list[] = $value["id"];
    }

    # 找协作的
	$coop_channal = share_res_list_get($_COOKIE["userid"],2);
	foreach ($coop_channal as $key => $value) {
		# code...
		$channal_list[] = $value["res_id"];
	}
}
if (count($channal_list) > 0) {
    $channel_place_holders = implode(',', array_fill(0, count($channal_list), '?'));
    $channel_query = " OR channal IN ($channel_place_holders)";
} else {
    $channel_query = "";
}

# 查询有阅读权限的channel 结束

$dbh = new PDO(_FILE_DB_SENTENCE_, "", "", array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
/* 开始一个事务，关闭自动提交 */

if (isset($_GET["sentences"])) {
    #查询句子编号列表
    $arrSent = explode(",", $_GET["sentences"]);
    /*  创建一个填充了和params相同数量占位符的字符串 */
    $place_holders = implode(',', array_fill(0, count($arrSent), '?'));
    $query = "SELECT * FROM sentence WHERE id IN ($place_holders) and (status = 30 {$channel_query} )";
    $stmt = $dbh->prepare($query);
    $stmt->execute($arrSent);
} else {
    $book = $_GET["book"];
    $para = $_GET["para"];
    $begin = $_GET["begin"];
    $end = $_GET["end"];
    $query = "SELECT * FROM sentence WHERE (book = ?  AND paragraph = ? AND begin = ? AND end = ? and strlen >0 and (status = 30 {$channel_query} ) ) order by modify_time DESC  ";
    $stmt = $dbh->prepare($query);
    $parm = array($book, $para, $begin, $end);
    $parm = array_merge_recursive($parm, $channal_list);
    $stmt->execute($parm);
}

$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
$channel_info = new Channal($redis);
$user_info = new UserInfo();

foreach ($Fetch as $key => $value) {
    # code...
	$Fetch[$key]["para"]=$value["paragraph"];

    $channel = $channel_info->getChannal($value["channal"]);
    if ($channel) {
		$Fetch[$key]["mypower"] = $channel_info->getPower($value["channal"]);
        $Fetch[$key]["c_name"] = $channel["name"];
        $Fetch[$key]["c_owner"] = $user_info->getName($channel["owner"]);
        $Fetch[$key]["channalinfo"] = $channel;
    }
	else{
		$Fetch[$key]["c_name"] = "unkow";
        $Fetch[$key]["c_owner"] = "unkow";
	}
	$Fetch[$key]["editor_name"]=$user_info->getName($value["editor"]);
	$Fetch[$key]["update_time"]=$value["modify_time"];

}

echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
