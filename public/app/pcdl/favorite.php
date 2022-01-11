<?php
include "./config.php";
include "./_pdo.php";

if(isset($_POST["id"])){
	$res_id=$_POST["id"];
}
else{
	echo "no res id";
	exit;
}
	
	//更新点击
	$dir= $dir_user_base.$_COOKIE["userid"];
	$db_file = $dir.'my_data.db';
	PDO_Connect("$db_file");

	$query="INSERT INTO favorite ('id','res_id','title','tag','user','time') VALUES (NULL,?,?,?,?,?)";
		$stmt = @PDO_Execute($query);
				if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
					$error = PDO_ErrorInfo();
					print_r($error[2]);
					break;
				}


?>