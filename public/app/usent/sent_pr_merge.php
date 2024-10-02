<?php
#更新一个句子
require_once "../config.php";
require_once "../public/function.php";
require_once "../usent/function.php";
require_once "../ucenter/active.php";
require_once "../share/function.php";
require_once "../redis/function.php";
require_once "../channal/function.php";

$db_pr = new SentPr();
$db_sent = new Sent_DB();
$channel = new Channal();

$updateDate=array();
$insertData=array();
$insertHistoray=array();

$respond["error"]=0;
$respond["message"]="";
$prData = $db_pr->getPrDataById($_POST["id"]);
if($prData){
	$channelPower = $channel->getPower($prData["channel"]);
	if($channelPower>=20){
		$dest = $db_sent->getSent($prData["book"],$prData["paragraph"],$prData["begin"],$prData["end"],$prData["channel"]);
		$newData = $prData;
		if($dest){
			#更新
			$newData["id"]=$dest["uid"];
			$newData["modify_time"]=mTime();
			$newData["landmark"]="";
			$updateDate[] = $newData;
			$insertHistoray[] = $newData;
		}
		else{
			#插入
			$newData["id"]=UUID::v4();;
			$newData["modify_time"]=mTime();
			$newData["landmark"]="";
			$insertData[] = $newData;
			$insertHistoray[] = $newData;
		}
		if($db_sent->update($updateDate)){
			$respond['update'] = count($updateDate);
			$respond['data'] = $newData;
		}
		else{
			$respond['message'] = $db_sent->getError();
			$respond['status'] = 1;
		}
		if($db_sent->insert($insertData)){
			$respond['insert'] = count($insertData);
	
		}else{
			$respond['message'] = $db_sent->getError();
			$respond['status'] = 1;
		}
		if($db_sent->historay($insertHistoray)){
			$respond['historay'] = count($insertHistoray);
	
		}else{
			$respond['message'] = $db_sent->getError();
			$respond['status'] = 1;
		}
	}
	else{
		$output["error"]=1;
		$output["message"]="没有写入权限";
	}
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);

?>