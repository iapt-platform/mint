<?php
//获取article内容

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once '../redis/function.php';
require_once "../article/function.php";



if(isset($_GET["id"])){
	//查询权限
	if(isset($_GET["collection_id"])){
		$collectionId = $_GET["collection_id"];
	}
	else{
		$collectionId = "";
	}
	$redis = redis_connect();
	$article = new Article($redis); 
	$power = $article->getPower($_GET["id"],$collectionId);
	if($power<10){
		$output = array();
		$output["title"]="Sorry对不起";
		$output["subtitle"]="No Power For Read<br>没有阅读权限";
		$output["summary"]="";
		$output["content"]="This is a **private** rescouce, reading need special <guide gid='power_set'><b>power</b></guide>.<br>该资源是**私密**资源，阅读需要<guide gid='power_set'><b>权限</b></guide>。";
		$output["owner"]="";
		$output["username"]=array("username"=>"","nickname"=>"");
		$output["status"]="";
		echo json_encode($output, JSON_UNESCAPED_UNICODE);
		exit;
	}
	#查询权限结束
	/*
    PDO_Connect(""._FILE_DB_USER_ARTICLE_);
    $id=$_GET["id"];
    $query = "SELECT * FROM article  WHERE id = ? ";
    $Fetch = PDO_FetchRow($query,array($id));
	*/
	$Fetch = $article->getInfo($_GET["id"]);
    if($Fetch){
		$Fetch["content"] = $article->getContent($_GET["id"]);
        $userinfo = new UserInfo();
        $user = $userinfo->getName($Fetch["owner"]);
        $Fetch["username"] = $user;
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>