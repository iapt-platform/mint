<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";
require_once "../article/function.php";
require_once "../redis/function.php";
require_once "../db/custom_book.php";


add_edit_event(_ARTICLE_EDIT_,$_POST["id"]);

$respond=array("status"=>0,"message"=>"");

# 检查是否有修改权限
$redis = redis_connect();
$article = new Article($redis);
$power = $article->getPower($_POST["id"]);
if($power<20){
	$respond["status"]=1;
    $respond["message"]="No Power For Edit";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

$_content = $_POST["content"];

/*
if(isset($_POST["import"]) && $_POST["import"]=='on'){
	#导入自定义书
	$custom_book = new CustomBook($redis);
	$_lang = explode("_",$_POST["lang"]);
	if(count($_lang)===3){
		$lang = $_lang[2];
	}
	else if(count($_lang)===1){
		$lang = $_lang[0];
	}
	else{
		$respond["status"]=1;
		$respond["message"]="无法识别的语言".$_POST["lang"];
		echo json_encode($respond, JSON_UNESCAPED_UNICODE);
		exit;
	}

	$respond = $custom_book->new($_POST["title"],$_content,$lang);
	if($respond["status"]==0){
		$_content = $respond["content"];
	}
	else{
		echo json_encode($respond, JSON_UNESCAPED_UNICODE);
		exit;
	}
}
*/

PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);

$query="UPDATE "._TABLE_ARTICLE_." SET title = ? , subtitle = ? , summary = ?, lang = ? , content = ?   , setting = ? , status = ? , updated_at=now()  , modify_time= ?   where  uid = ?  ";
$sth = $PDO->prepare($query);
$sth->execute(array($_POST["title"] , $_POST["subtitle"] ,$_POST["summary"] ,$_POST["lang"], $_content  , "{}" , $_POST["status"] ,   mTime() , $_POST["id"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
	if($redis){
		$redis->del("article://".$_POST["id"]);
		$redis->del("power://article/".$_POST["id"]);
	}

}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>
