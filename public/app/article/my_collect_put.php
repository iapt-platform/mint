<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

$respond=array("status"=>0,"message"=>"");
if(!isset($_COOKIE["userid"])){
	#不登录不能新建
	$respond['status']=1;
	$respond['message']="no power create article";
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
}
if(!isset($_POST["title"])){
	#无标题不能新建
	$respond['status']=1;
	$respond['message']="no title";
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
}

$uuid = UUID::v4();
add_edit_event(_COLLECTION_NEW_,$uuid);


PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
$query="INSERT INTO "._TABLE_COLLECTION_." ( 
        id,
		uid ,  
		title  , 
		subtitle  , 
		summary , 
		article_list   , 
		owner, 
		owner_id, 
		editor_id, 
		lang  , 
		status  , 
		create_time , 
		modify_time    
		)  VALUES  ( ? , ? , ? , ?  , ? , ? , ? , ? , ? , ? , ? , ? , ? ) ";
$sth = $PDO->prepare($query);
$sth->execute(array(
                $snowflake->id() , 
                $uuid , 
                $_POST["title"] , 
                "" ,
                "",
                 "[]" ,  
                 $_COOKIE["user_uid"] ,  
                 $_COOKIE["user_id"],  
                 $_COOKIE["user_id"], 
                 "" , 
                 $_POST["status"] , 
                 mTime() ,  
                 mTime() 
                 ));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>