<?php
#新建channel
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

$respond=array("status"=>0,"message"=>"");
if(isset($_COOKIE["userid"])){
	PDO_Connect(_FILE_DB_CHANNAL_,_DB_USERNAME_,_DB_PASSWORD_);
	$query="INSERT INTO "._TABLE_CHANNEL_." ( id, uid,  owner_uid  , editor_id, name  , summary ,  status  , lang, create_time , modify_time    )  VALUES  ( ?, ? , ? , ? , ? , ? , ? , ? , ? , ?  ) ";
	$sth = $PDO->prepare($query);
	$sth->execute(array($snowflake->id() , UUID::v4() , $_COOKIE["user_uid"] , $_COOKIE["user_id"] , $_POST["name"] , "" , $_POST["status"] ,$_POST["lang"]  ,  mTime() ,  mTime() ));
	$respond=array("status"=>0,"message"=>"");
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
		$error = PDO_ErrorInfo();
		$respond['status']=1;
		$respond['message']=$error[2];
	}	
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>