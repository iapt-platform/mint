<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
$respond=array("status"=>0,"message"=>"");
if(isset($_COOKIE["userid"])){
	PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
	$query="INSERT INTO channal ( id,  owner  , name  , summary ,  status  , create_time , modify_time , receive_time   )  VALUES  ( ? , ? , ? , ? , ? , ? , ? , ?  ) ";
	$sth = $PDO->prepare($query);
	$sth->execute(array(UUID::v4() , $_COOKIE["userid"] , $_POST["name"] , "" ,$_POST["status"]  ,  mTime() ,  mTime() , mTime() ));
	$respond=array("status"=>0,"message"=>"");
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
		$error = PDO_ErrorInfo();
		$respond['status']=1;
		$respond['message']=$error[2];
	}	
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>