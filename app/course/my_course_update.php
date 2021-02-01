<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);

$query="UPDATE course SET  title = ? , subtitle = ? ,  summary = ? , teacher = ?  , tag = ?  , lang = ?  , attachment = ? , status = ? , receive_time = ?  , modify_time = ?   where  id = ?  ";
$sth = $PDO->prepare($query);

$sth->execute(array( $_POST["title"] , $_POST["subtitle"] ,  $_POST["summary"] ,   $_POST["teacher"]  ,  $_POST["tag"] ,  $_POST["lang"] , $_POST["attachment"] ,$_POST["status"] , mTime() , mTime() , $_POST["course"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
	$respond['status']=0;
	$respond['message']="成功";
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>