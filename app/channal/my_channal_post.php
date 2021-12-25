<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../channal/function.php';
require_once '../redis/function.php';
require_once '../hostsetting/function.php';
$respond=array("status"=>0,"message"=>"");

#先查询对此channal是否有权限修改
PDO_Connect(_FILE_DB_CHANNAL_);
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

$channelOldInfo = $channel->getChannal($_POST["id"]);

$query="UPDATE channal SET name = ? ,  summary = ?,  status = ? , lang = ? , receive_time= ?  , modify_time= ?   where  id = ?  ";
$sth = $PDO->prepare($query);
$sth->execute(array($_POST["name"] , $_POST["summary"], $_POST["status"] , $_POST["lang"] ,  mTime() , mTime() , $_POST["id"]));
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
    $query="UPDATE sentence SET language = ?  , status = ? where  channal = ?  ";
    $sth = PDO_Execute($query,array($_POST["lang"],$_POST["status"],$_POST["id"]));
    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status']=1;
        $respond['message']=$error[2];
    }

	// 设置 逐词译库可见性
	PDO_Connect(_FILE_DB_USER_WBW_);
	$query="UPDATE "._TABLE_USER_WBW_BLOCK_." SET lang = ?  , status = ? where  channal = ?  ";
	$sth = PDO_Execute($query,array($_POST["lang"],$_POST["status"],$_POST["id"]));
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
		$error = PDO_ErrorInfo();
		$respond['status']=1;
		$respond['message']=$error[2];
	}	
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>