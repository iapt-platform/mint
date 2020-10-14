<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

$output  = array();
$respond = array();
if(isset($_POST["id"])){
    $data = json_decode($_POST["data"]);
    $title = $_POST["title"];
    PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
    $article_id=$_POST["id"];
    $query = "DELETE FROM article_list WHERE article_id = ? ";
     PDO_Execute($query,array($article_id));
     if(count($data)>0){
        /* 开始一个事务，关闭自动提交 */
        $PDO->beginTransaction();
        $query = "INSERT INTO article_list (collect_id, article_id,level,title) VALUES (?, ?, ? , ?)";
        $sth = $PDO->prepare($query);
        foreach ($data as $row) {
            $sth->execute(array($row,$article_id,1,$title));
        }
        $PDO->commit();
        if (!$sth || ($sth && $sth->errorCode() != 0)) {
            /*  识别错误且回滚更改  */
            $PDO->rollBack();
            $error = PDO_ErrorInfo();
            $respond['status']=1;
            $respond['message']=$error[2];
        }
        else{
            $respond['status']=0;
            $respond['message']="成功";
        }	         
     }

	echo json_encode($respond, JSON_UNESCAPED_UNICODE);

}

?>