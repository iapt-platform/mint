<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect(""._FILE_DB_COURSE_);
$query="SELECT course_id from lesson  where  id = ?  ";
$course_id = PDO_FetchOne($query,array($_POST["lesson"]));

$query="UPDATE course SET  receive_time= ?  , modify_time= ?   where  id = ?  ";
PDO_Execute($query,array(mTime(),mTime(),$course_id));

$query="UPDATE lesson SET title = ? , subtitle = ? , date = ? , duration = ? , live = ? , replay = ? , summary = ? , teacher = ?  , attachment = ?, receive_time= ?  , modify_time= ?   where  id = ?  ";
$sth = $PDO->prepare($query);

$data = strtotime($_POST["lesson_date"]);
$time = strtotime($_POST["lesson_time"]) - strtotime("today");
$timezone = $_POST["lesson_timezone"];
$datatime = ($data+$time+$timezone*60)*1000;
$duration = strtotime($_POST["duration"]) - strtotime("today");

$sth->execute(array($_POST["title"] , $_POST["subtitle"] ,$datatime, $duration , $_POST["live"] , $_POST["replay"] , $_POST["summary"] ,  $_POST["teacher"] , $_POST["attachment"] ,  mTime() , mTime() , $_POST["lesson"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
	$respond["status"]= 1;
}
else{
	$respond['status']=0;
	$respond['message']="成功";
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>