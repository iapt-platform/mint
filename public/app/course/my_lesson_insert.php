<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect(""._FILE_DB_COURSE_);

$query="UPDATE course SET  receive_time= ?  , modify_time= ?   where  id = ?  ";
PDO_Execute($query,array(mTime(),mTime(),$_POST["course_id"]));

$query = "INSERT INTO lesson (id, course_id,  live, replay, title,  subtitle ,  date ,  duration  ,  creator,  tag,  summary,  status,  cover,  teacher, attachment , lang , speech_lang , create_time , modify_time , receive_time ) 
				 VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )";

$sth = $PDO->prepare($query);

/*
$data = strtotime($_POST["lesson_date"]);
$time = strtotime($_POST["lesson_time"]) - strtotime("today");
$datatime = ($data+$time)*1000;
$duration = strtotime($_POST["duration"]) - strtotime("today");
*/

$data = strtotime($_POST["lesson_date"]);
$time = strtotime($_POST["lesson_time"]) - strtotime("today");
$timezone = $_POST["lesson_timezone"];
$datatime = ($data+$time+$timezone*60)*1000;
$duration = strtotime($_POST["duration"]) - strtotime("today");

$sth->execute(array(UUID::v4(), $_POST["course_id"] , $_POST["live"] , $_POST["replay"] ,$_POST["title"] , $_POST["subtitle"]  , $datatime  , $duration  , $_COOKIE["userid"] , $_POST["tag"] ,$_POST["summary"] , 1, $_POST["cover"] ,$_POST["teacher"] , $_POST["attachment"] , $_POST["lang"] , $_POST["speech_lang"] ,mTime() , mTime() ,  mTime() ));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
	$respond['status']=0;
	$respond['message']="成功";
	$respond['course_id']=$_POST["course_id"];
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>