<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';

$_userinfo = new UserInfo();

PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
$query = "SELECT * from channal where owner = ?   limit 0,100";
$Fetch_my = PDO_FetchAll($query,array($_COOKIE["userid"]));

# 找协作的
$Fetch_coop = array();
$query = "SELECT channal_id FROM cooperation WHERE  user_id = ? ";
$coop_channal = PDO_FetchAll($query,array($_COOKIE["userid"]));
if(count($coop_channal)>0){
    foreach ($coop_channal as $key => $value) {
        # code...
        $channal[]=$value["channal_id"];
    }
    /*  创建一个填充了和params相同数量占位符的字符串 */
    $place_holders = implode(',', array_fill(0, count($channal), '?'));
    $query = "SELECT * FROM channal WHERE id IN ($place_holders) order by owner";
    $Fetch_coop = PDO_FetchAll($query,$channal);
}
$all = array_merge_recursive($Fetch_my,$Fetch_coop);
$output = array();
foreach ($all as $key => $value) {
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