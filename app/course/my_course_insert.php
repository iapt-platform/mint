<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect(""._FILE_DB_COURSE_);

$query = "INSERT INTO course (id,  title,  subtitle, creator, tag, summary, status, cover, teacher,  lang , speech_lang ,attachment, lesson_num , create_time , modify_time , receive_time ) 
                      VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?  )";
$sth = $PDO->prepare($query);

$sth->execute(array(UUID::v4() ,$_POST["title"] , $_POST["subtitle"]  , $_COOKIE["userid"] , $_POST["tag"] ,$_POST["summary"] , 1, $_POST["cover"] ,$_POST["teacher"] ,  $_POST["lang"] , "" ,$_POST["attachment"] , 0 ,mTime() , mTime() ,  mTime() ));
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