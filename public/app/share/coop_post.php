<?php
//查询term字典

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../group/function.php';
require_once "../redis/function.php";
require_once "../collect/function.php";

$respond['status']=0;
$respond['message']="成功";
if(isset($_POST["res_id"])){
	$redis = redis_connect();
	PDO_Connect(_FILE_DB_USER_SHARE_,_DB_USERNAME_, _DB_PASSWORD_);
    $query = "UPDATE "._TABLE_USER_SHARE_." set power = ? WHERE res_id=? and cooperator_id = ? ";
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
			if($redis){
				switch ((int)$_POST["res_type"]) {
					case 1:
						# pcs
						$redis->del("power://pcs/".$_POST["res_id"]);
						break;
					case 2:
						# channel
						$redis->del("power://channel/".$_POST["res_id"]);
						break;
					case 3:
						# code...
						$redis->del("power://article/".$_POST["res_id"]);
						break;
					case 4:
						# 文集
						$redis->del("power://collection/".$_POST["res_id"]);
						# 删除文章列表权限缓存
						$collection = new CollectInfo($redis);
						$articleList = $collection->getArticleList($_POST["res_id"]);
						foreach ($articleList as $key => $value) {
							# code...
							$redis->del("power://article/".$value);
						}
						break;											
					default:
						# code...
						break;
				}
			}
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