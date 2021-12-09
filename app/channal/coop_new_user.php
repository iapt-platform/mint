<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond=array("status"=>0,"message"=>"");
if(isset($_COOKIE["userid"]) && isset($_POST["channel_id"])){
	PDO_Connect(""._FILE_DB_CHANNAL_);
	$query="INSERT INTO cooperation ( channal_id , user_id  ,type  , power  )  
	VALUES  (  ? , ? , ? , ? ) ";
	$sth = $PDO->prepare($query);
	$sth->execute(array($_POST["channel_id"],$_POST["userid"] , 0 , 1 ));
	$respond=array("status"=>0,"message"=>"");
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
		$error = PDO_ErrorInfo();
		$respond['status']=1;
		$respond['message']=$error[2];
	}	
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>