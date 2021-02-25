<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";

$respond=array("status"=>0,"message"=>"");
PDO_Connect(""._FILE_DB_USER_ARTICLE_);

$query="INSERT INTO collect ( id,  title  , subtitle  , summary , article_list   , owner, lang  , status  , create_time , modify_time , receive_time   )  VALUES  ( ? , ? , ? , ?  , ? , ? , ? , ? , ? , ? , ? ) ";
$sth = $PDO->prepare($query);
$uuid = UUID::v4();
add_edit_event(_COLLECTION_NEW_,$uuid);
$sth->execute(array($uuid , $_POST["title"] , "" ,"", "[]" ,  $_COOKIE["userid"] , "" , 1 , mTime() ,  mTime() , mTime() ));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>