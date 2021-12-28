<?php
require '../vendor/autoloader.php';

$output=array();
$output["error"]=0;
$output["message"]="";


if(isset($_POST["userid"]) && isset($_POST["password"]) ){
	require_once "../config.php";
	require_once "../public/load_lang.php";
	require_once "../ucenter/function.php";	
	require_once '../redis/function.php';
	require_once '../public/function.php';
	
	$redis=redis_connect();
	if($redis==false){
		$output["error"]=2;
		$output["message"]="redis connect fail";
		echo json_encode($output, JSON_UNESCAPED_UNICODE);
		exit;
	}
	
	$user = new UserInfo();
	if($user->check_password($_POST["userid"],$_POST["password"])){
		if(isset($_POST["server"])){
			#本地登录
			#在远程主机验证用户身份
			$phpFile = $_POST["server"]."/app/sync/login.php";
			$client = new \GuzzleHttp\Client();
			$response = $client->request('POST', $phpFile,['verify' => false,'form_params'=>['userid'=>$_POST["userid"],'password'=>$_POST["password"]]]);
			$serveMsg=(string)$response->getBody();
			$arrServerMsg = json_decode($serveMsg,true);
			if($arrServerMsg["error"]==0){
				#验证成功
				setcookie ( "sync_userid" ,  $_POST["userid"] , 0 ,  "/" , "" ,  false , true );
				setcookie ( "sync_server" ,  $_POST["server"] , 0 ,  "/" , "" ,  false , true );				
				$redis->hset("sync://key",$_POST["userid"],$arrServerMsg["key"]);
				$output["message"]="本机登录成功<br>服务器验证成功<br>".$arrServerMsg["message"];
				$output["message"].="<a href='index.php'>开始同步</a>";
			}
			else{
				#验证失败
				$output["error"]=$arrServerMsg["error"];
				$output["message"]="本机登录成功<br>服务器验证失败".$arrServerMsg["message"];
				$output["message"]=$arrServerMsg["message"];
			}
			
			echo json_encode($output, JSON_UNESCAPED_UNICODE);
		}
		else{
			#服务器登录成功
			
			$key=UUID::v4();
			$redis->hset("sync://key",$_POST["userid"],$key);
			$output["error"]=0;
			$output["message"]="服务器登录成功";
			$output["key"]=$key;
			echo json_encode($output, JSON_UNESCAPED_UNICODE);
		}
	}
	else{
		$output["error"]=1;
		$output["message"]="不正确的用户名或密码";
		echo json_encode($output, JSON_UNESCAPED_UNICODE);
	}
}
else{
	$output["error"]=2;
	$output["message"]="参数错误";
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
}
?>