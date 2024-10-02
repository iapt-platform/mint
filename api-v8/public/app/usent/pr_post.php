<?php
include("../log/pref_log.php");
require_once "../usent/function.php";
require_once "../channal/function.php";
require_once "../ucenter/function.php";
require_once "../redis/function.php";

//回传数据
$respond = array("status" => 0, "message" => "");
$respond['id'] = $_POST["id"];
$respond['book'] = $_POST["book"];
$respond['para'] = $_POST["para"];
$respond['begin'] = $_POST["begin"];
$respond['end'] = $_POST["end"];
$respond['channal'] = $_POST["channel"];
$respond['text'] = $_POST["text"];
$respond['editor'] = $_COOKIE["userid"];
$respond['commit_type'] = 1;

$redis = redis_connect();

$channel_info = new Channal();
$user_info = new UserInfo();
$pr = new SentPr($redis);
$result = $pr->setPrData($_POST["id"],$_POST["text"]);
if(!$result){
	$respond['status']=1;
	$respond['message']="error";
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);

PrefLog();