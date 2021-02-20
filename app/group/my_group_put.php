<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond=array("status"=>0,"message"=>"");
if(isset($_COOKIE["userid"])){
	PDO_Connect("sqlite:"._FILE_DB_GROUP_);
	$query="INSERT INTO group_info ( id,  parent  , name  , description ,  status , owner ,create_time )  
	                       VALUES  ( ?, ? , ? , ? , ? , ?  ,? ) ";
	$sth = $PDO->prepare($query);
	$newid = UUID::v4();
	$sth->execute(array( $newid,$_POST["parent"], $_POST["name"], "" ,1 ,$_COOKIE["userid"] , mTime() ));
	$respond=array("status"=>0,"message"=>"");
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
		$error = PDO_ErrorInfo();
		$respond['status']=1;
		$respond['message']=$error[2];
	}	

	$query="INSERT INTO group_member (  user_id  , group_id  , power , group_name , level ,  status )  
		VALUES  (  ? , ? , ? , ? , ?  ,? ) ";
	$sth = $PDO->prepare($query);
	if($_POST["parent"]==0){
		$level = 0;
	}
	else{
		$level = 1;
	}
	$sth->execute(array($_COOKIE["userid"] ,$newid, 1 , $_POST["name"], $level ,1 ));
	$respond=array("status"=>0,"message"=>"");
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
	}	
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>