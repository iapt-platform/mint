<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);

$query="UPDATE lesson SET title = ? , subtitle = ? , date = ? , duration = ? , live = ? , replay = ? , summary = ? , teacher = ?  , receive_time= ?  , modify_time= ?   where  id = ?  ";
$sth = $PDO->prepare($query);
$data = strtotime($_POST["lesson_date"]);
$time = strtotime($_POST["lesson_time"]) - strtotime("today");
$datatime = ($data+$time)*1000;
$duration = strtotime($_POST["duration"]) - strtotime("today");
$sth->execute(array($_POST["title"] , $_POST["subtitle"] ,$datatime, $duration , $_POST["live"] , $_POST["replay"] , $_POST["summary"] ,  $_POST["teacher"] ,  mTime() , mTime() , $_POST["lesson"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
	echo "<div style=''>Lesson 数据修改失败：{$error[2]}</div>";
}
else{
	$respond['status']=0;
	$respond['message']="成功";
	echo "<div style=''>Lesson 数据修改成功</div>";
	echo $d=strtotime("today");

	echo  strtotime($_POST["lesson_date"])."<br>";
	echo  strtotime($_POST["lesson_time"])."<br>";
	echo  (strtotime($_POST["duration"]) - $d)."<br>";
}
//echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>