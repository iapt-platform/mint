<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond=array("status"=>0,"message"=>"");
//处理文件上传

if(isset($_FILES["cover"])){
	if ((($_FILES["cover"]["type"] == "image/gif")
	|| ($_FILES["cover"]["type"] == "image/jpeg")
	|| ($_FILES["cover"]["type"] == "image/png"))
	&& ($_FILES["cover"]["size"] < 2000000))
	{
	if ($_FILES["cover"]["error"] > 0)
		{
			$respond['status']=1;
			$respond['message']=$_FILES["cover"]["error"];
		}
	else
		{
		move_uploaded_file($_FILES["cover"]["tmp_name"],
		_DIR_IMAGES_COURSE_."/" . $_POST["course"].".jpg");
		}
	}
	else
	{
	echo "Invalid file";
	$respond['status']=1;
	$respond['message']="Invalid file";
	}	
}


//处理文件上传结束

PDO_Connect(""._FILE_DB_COURSE_);

$query="UPDATE course SET  title = ? , subtitle = ? ,  summary = ? , teacher = ?  , tag = ?  , lang = ?  , attachment = ? , status = ? , receive_time = ?  , modify_time = ?   where  id = ?  ";
$sth = $PDO->prepare($query);

$sth->execute(array( $_POST["title"] , $_POST["subtitle"] ,  $_POST["summary"] ,   $_POST["teacher"]  ,  $_POST["tag"] ,  $_POST["lang"] , $_POST["attachment"] ,$_POST["status"] , mTime() , mTime() , $_POST["course"]));

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