<?php
/*
修改channel
*/
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../channal/function.php';
require_once '../redis/function.php';
require_once '../hostsetting/function.php';
$respond=array("status"=>0,"message"=>"");

#先查询对此channal是否有权限修改
PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
$cooperation = 0;
if(isset($_POST["id"])){
	$redis = redis_connect();
	$channel = new Channal($redis);
	$channelPower = $channel->getPower($_POST["id"]);
}
else{
    $respond["status"] = 1;
    $respond["message"] = 'error channal id';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}
if($channelPower<30){
    $respond["status"] = 1;
    $respond["message"] = 'error 无修改权限';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

if(!isset($_POST["type"])){
    $respond["status"] = 1;
    $respond["message"] = 'Error： no [type]  ';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

$channelOldInfo = $channel->getChannal($_POST["id"]);

$query="UPDATE "._TABLE_CHANNEL_." SET editor_id=?, name = ? ,  summary = ?, type = ?, status = ? , lang = ? , updated_at = now()  , modify_time= ?   where uid = ?  ";
$sth = $PDO->prepare($query);
$sth->execute(array($_COOKIE["user_id"],$_POST["name"] , $_POST["summary"], $_POST["type"], $_POST["status"] , $_POST["lang"] ,  mTime() , $_POST["id"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
	if($redis){
		if($channelOldInfo["status"]!=$_POST["status"]){
			$redis->del("power://channel/".$_POST["id"]);
		}
	}
    // 设置 句子库和逐词译库可见性
    PDO_Connect(_FILE_DB_SENTENCE_);
    $query="UPDATE "._TABLE_SENTENCE_." SET language = ?  , status = ? where  channel_uid = ?  ";
    $sth = PDO_Execute($query,array($_POST["lang"],$_POST["status"],$_POST["id"]));
    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status']=1;
        $respond['message']=$error[2];
    }

	// 设置 逐词译库可见性
	PDO_Connect(_FILE_DB_USER_WBW_);
	$query="UPDATE "._TABLE_USER_WBW_BLOCK_." SET lang = ?  , status = ? where  channel_uid = ?  ";
	$sth = PDO_Execute($query,array($_POST["lang"],$_POST["status"],$_POST["id"]));
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
		$error = PDO_ErrorInfo();
		$respond['status']=1;
		$respond['message']=$error[2];
	}	
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>