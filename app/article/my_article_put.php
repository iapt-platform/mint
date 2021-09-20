<?php
/*
新建文章
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";

$respond=array("ok"=>true,"message"=>"");
if(!isset($_COOKIE["userid"])){
	#不登录不能新建
	$respond['ok']=false;
	$respond['message']="no power create article";
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
}
if(!isset($_POST["title"])){
	#无标题不能新建
	$respond['ok']=false;
	$respond['message']="no title";
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
}
if(isset($_POST["content"])){
	$content = $_POST["content"];
}
else{
	$content = "";
}
PDO_Connect(_FILE_DB_USER_ARTICLE_);

$query="INSERT INTO article ( id,  title  , subtitle  , summary , content   , tag  , owner, setting  , status  , create_time , modify_time , receive_time   )  VALUES  ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? ) ";
$sth = $PDO->prepare($query);
$uuid = UUID::v4();
//写入日志
add_edit_event(_ARTICLE_NEW_,$uuid);
#新建文章默认私有
$sth->execute(array($uuid , $_POST["title"] , "" ,"", $content , "" , $_COOKIE["userid"] , "{}" , 10 , mTime() ,  mTime() , mTime() ));

if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['ok']=false;
	$respond['message']=$error[2];
}
else{
	$respond['data']=["id"=>$uuid,"title"=>$_POST["title"]];
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>