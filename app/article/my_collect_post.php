<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";

$respond=array("status"=>0,"message"=>"");

PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);

$query="UPDATE collect SET title = ? , subtitle = ? , summary = ?, article_list = ?  ,  status = ? , lang = ? , receive_time= ?  , modify_time= ?   where  id = ?  ";
$sth = $PDO->prepare($query);
$sth->execute(array($_POST["title"] , $_POST["subtitle"] ,$_POST["summary"], $_POST["article_list"] , $_POST["status"] , $_POST["lang"] ,  mTime() , mTime() , $_POST["id"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
    # 更新 article_list 表
    $query = "DELETE FROM article_list WHERE collect_id = ? ";
     PDO_Execute($query,array($_POST["id"]));
     $arrList = json_decode($_POST["article_list"]);
     if(count($arrList)>0){
        /* 开始一个事务，关闭自动提交 */
        $PDO->beginTransaction();
        $query = "INSERT INTO article_list (collect_id, article_id,level,title) VALUES ( ?, ?, ? , ? )";
        $sth = $PDO->prepare($query);
        foreach ($arrList as $row) {
            $sth->execute(array($_POST["id"],$row->article,$row->level,$row->title));
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