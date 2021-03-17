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
	$PDO->beginTransaction();
    $query = "INSERT  INTO share_cooperator ('id','res_id','res_type','cooperator_id','cooperator_type','power','create_time','modify_time','is_deleted') VALUES (null,?,?,?,?,?,?,?,?) ";
	$sth = $PDO->prepare($query);
	$data = json_decode($_POST["user_info"]);
	foreach ($data as $key => $user) {
		# code...
		$sth->execute(array($_POST["res_id"],
							$_POST["res_type"],
							$user->id,
							$user->type,
							$_POST["power"],
							mTime(),
							mTime(),
							0
						));
	}
	$PDO->commit();
        
	if (!$sth || ($sth && $sth->errorCode() != 0)) {
		/*  识别错误且回滚更改  */
		$PDO->rollBack();
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
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
}
else{
	$respond['status']=1;
	$respond['message']="no res id";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
}
?>