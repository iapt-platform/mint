<?php 
include("../public/config.php");

$USER_ID = "";
if(isset($_COOKIE["username"]) && !empty($_COOKIE["username"])){
 $USER_ID = $_COOKIE["userid"];
}

if($USER_ID!=""){
	$user_path=$dir_user_base.$USER_ID."/";
	$filename=$user_path.$_POST["filename"];
	switch($_POST["op"]){
		case "save":
			$data=$_POST["data"];
			//save data file
			echo file_put_contents($filename,$data);
			echo("Successful");
			break;
		case "read":
			echo file_get_contents($filename);
		break;
	}
}
else{
	echo("user id is vailed");
}
?>
