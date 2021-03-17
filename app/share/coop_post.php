<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../group/function.php';

$respond['status']=0;
$respond['message']="成功";
if(isset($_POST["res_id"])){
	PDO_Connect(_FILE_DB_USER_SHARE_);

    $query = "UPDATE share_cooperator set power = ? WHERE res_id=? and cooperator_id = ? ";
	$sth = $PDO->prepare($query);
	if($sth)
	{
		# code...
		$sth->execute(array($_POST["power"],
							$_POST["res_id"],
							$_POST["user_id"]
						));
		if (!$sth || ($sth && $sth->errorCode() != 0)) {
			/*  识别错误  */
			$error = PDO_ErrorInfo();
			$respond['status']=1;
			$respond['message']=$error[2];
			echo json_encode($respond, JSON_UNESCAPED_UNICODE);
			exit;
		}
		else{
			$respond['status']=0;
			$respond['message']="成功";
		}							
	}

    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
}
else{
	$respond['status']=1;
	$respond['message']="no res id";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
}
?>