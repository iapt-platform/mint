<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);

$query = "INSERT INTO course (id,  title,  subtitle, creator, tag, summary, status, cover, teacher,  lang , speech_lang , lesson_num , create_time , modify_time , receive_time ) 
                      VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?  )";
$sth = $PDO->prepare($query);

$sth->execute(array(UUID::v4() ,$_POST["title"] , $_POST["subtitle"]  , $_COOKIE["userid"] , $_POST["tag"] ,$_POST["summary"] , 1, $_POST["cover"] ,$_POST["teacher"] ,  $_POST["lang"] , "" , 0 ,mTime() , mTime() ,  mTime() ));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
	echo "<div style=''>Course 数据添加失败：{$error[2]}</div>";
}
else{
	$respond['status']=0;
	$respond['message']="成功";
	echo "<div style=''>Course 数据添加成功</div>";
}
//echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>