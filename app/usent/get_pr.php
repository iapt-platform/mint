<?php

require_once "../usent/function.php";
require_once "../channal/function.php";
require_once "../ucenter/function.php";
require_once "../redis/function.php";

$redis = redis_connect();

$channel_info = new Channal();
$user_info = new UserInfo();
$pr = new SentPr($redis);
$result = $pr->getPrData($_POST["book"],$_POST["para"],$_POST["begin"],$_POST["end"],$_POST["channel"]);
foreach ($result as $key => $value) {
	# code...
	$result[$key]["editor_name"]=$user_info->getName($value["editor"]);
	$result[$key]["update_time"]=$value["modify_time"];
	$result[$key]["channal"]=$value["channel"];
	$result[$key]["para"]=$value["paragraph"];
	$result[$key]["channalinfo"] = $channel_info->getChannal($value["channel"]);
	$result[$key]["mypower"] = $channel_info->getPower($value["channel"]);
	if(isset($_COOKIE["userid"])){
		if($value["editor"]==$_COOKIE["userid"]){
			$result[$key]["is_pr_editor"] =true;
		}
		else{
			$result[$key]["is_pr_editor"] =false;
		}
	}
	else{
		$result[$key]["is_pr_editor"] =false;
	}
	$result[$key]["is_pr"] =true;
}
echo json_encode($result, JSON_UNESCAPED_UNICODE);
