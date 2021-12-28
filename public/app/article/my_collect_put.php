<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";

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
add_edit_event(_COLLECTION_NEW_,$uuid);

PDO_Connect(""._FILE_DB_USER_ARTICLE_);
$query="INSERT INTO collect ( id,  title  , subtitle  , summary , article_list   , owner, lang  , status  , create_time , modify_time , receive_time   )  VALUES  ( ? , ? , ? , ?  , ? , ? , ? , ? , ? , ? , ? ) ";
$sth = $PDO->prepare($query);
$uuid = UUID::v4();
$sth->execute(array($uuid , $_POST["title"] , "" ,"", "[]" ,  $_COOKIE["userid"] , "" , $_POST["status"] , mTime() ,  mTime() , mTime() ));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>