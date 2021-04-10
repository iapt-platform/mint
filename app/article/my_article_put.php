<?php
/*
新建文章
*/
require_once "../path.php";
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
PDO_Connect(_FILE_DB_USER_ARTICLE_);

$query="INSERT INTO article ( id,  title  , subtitle  , summary , content   , tag  , owner, setting  , status  , create_time , modify_time , receive_time   )  VALUES  ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? ) ";
$sth = $PDO->prepare($query);
$uuid = UUID::v4();
//写入日志
add_edit_event(_ARTICLE_NEW_,$uuid);
#新建文章默认私有
$sth->execute(array($uuid , $_POST["title"] , "" ,"", "" , "" , $_COOKIE["userid"] , "{}" , 10 , mTime() ,  mTime() , mTime() ));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>