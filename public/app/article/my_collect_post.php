<?php
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../collect/function.php';
require_once "../ucenter/active.php";
require_once "../redis/function.php";
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

$respond=array("status"=>0,"message"=>"");
if(!isset($_COOKIE["userid"])){
	#不登录不能新建
	$respond['status']=1;
	$respond['message']="no power create article";
	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
	exit;
}
# 检查当前用户是否有修改权限
$redis = redis_connect();
$collection = new CollectInfo($redis); 
$power = $collection->getPower($_POST["id"]);
if($power<20){
	$respond["status"]=1;
    $respond["message"]="No Power For Edit";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}


add_edit_event(_COLLECTION_EDIT_,$_POST["id"]);

PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);

$query="UPDATE "._TABLE_COLLECTION_." SET title = ? , subtitle = ? , summary = ?, article_list = ?  ,  status = ? , lang = ? , updated_at= now()  , modify_time= ?   where  uid = ?  ";
$sth = $PDO->prepare($query);
$sth->execute(array($_POST["title"] , $_POST["subtitle"] ,$_POST["summary"], $_POST["article_list"] , $_POST["status"] , $_POST["lang"] ,  mTime() ,  $_POST["id"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
	if($redis){
		$redis->del("collection://".$_POST["id"]);
		$redis->del("power://collection/".$_POST["id"]);
	}
    # 更新 article_list 表
    $query = "DELETE FROM "._TABLE_ARTICLE_COLLECTION_." WHERE collect_id = ? ";
    PDO_Execute($query,array($_POST["id"]));
    $arrList = json_decode($_POST["article_list"]);
    if(count($arrList)>0){
        /* 开始一个事务，关闭自动提交 */
        $PDO->beginTransaction();
        $query = "INSERT INTO "._TABLE_ARTICLE_COLLECTION_." (
                            id,
                            collect_id, 
                            article_id,
                            level,
                            title,
                            children
                            ) VALUES (?, ? , ?, ?, ? , ? )";
        $sth = $PDO->prepare($query);
        foreach ($arrList as $row) {
            $sth->execute(array(
                            $snowflake->id(),
                            $_POST["id"],
                            $row->article,
                            $row->level,
                            $row->title,
                            $row->children
                            ));
			if($redis){
				#删除article权限缓存
				$redis->del("power://article/".$row->article);
			}
        }
        $PDO->commit();
        if (!$sth || ($sth && $sth->errorCode() != 0)) {
            /*  识别错误且回滚更改  */
            $PDO->rollBack();
            $error = PDO_ErrorInfo();
            $respond['status']=1;
            $respond['message']=$error[2];
        }
    }
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>