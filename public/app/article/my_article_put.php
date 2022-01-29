<?php
/*
新建文章
*/
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

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
PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);

$query="INSERT INTO "._TABLE_ARTICLE_." 
        ( 
            id,
			uid,  
			title  , 
			subtitle  , 
			summary , 
			content  , 
			owner, 
			owner_id, 
			editor_id, 
			setting  , 
			status  , 
			create_time , 
			modify_time    
			)  VALUES  ( ? , ? ,  ? , ? , ? , ? , ? , ? , ? ,? ,?, ? , ? ) ";
$sth = $PDO->prepare($query);
$uuid = UUID::v4();
//写入日志
add_edit_event(_ARTICLE_NEW_,$uuid);
#新建文章默认私有
$sth->execute(array(
                $snowflake->id() , 
                $uuid , 
                $_POST["title"] , 
                "" ,
                "", 
                $content  , 
                $_COOKIE["user_uid"] , 
                $_COOKIE["user_id"] , 
                $_COOKIE["user_id"] , 
                "{}" , 
                10 , 
                mTime() ,  
                mTime()  
                ));

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