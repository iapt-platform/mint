<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

$output  = array();
$respond = array();
$respond['status']=0;
$respond['message']="";
$respond['id']=$_POST["id"];

if(isset($_POST["id"])){
    $dirty_collect = array();
    $data = json_decode($_POST["data"]);
    $title = $_POST["title"];
    PDO_Connect(""._FILE_DB_USER_ARTICLE_);
    $article_id=$_POST["id"];
    //找出脏的collect
    $query = "SELECT collect_id FROM article_list  WHERE article_id = ? ";
    $collect = PDO_FetchAll($query,array($article_id));
    foreach ($collect as $key => $value) {
        # code...
        $dirty_collect[$value["collect_id"]] = 1;
    }
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
     }

     # 更新collect
     $query = "SELECT collect_id FROM article_list WHERE article_id  = ?";
     $collect = PDO_FetchAll($query,array($article_id));
     foreach ($collect as $key => $value) {
        # code...
        $dirty_collect[$value["collect_id"]] = 1;
    }
     foreach ($dirty_collect as $key => $value) {
         # code...
         $query = "SELECT level,article_id as article , title FROM article_list WHERE collect_id  = ?";
         $collect_info = PDO_FetchAll($query,array($key));
         $query = "UPDATE collect SET article_list = ? WHERE id = ? ";
         $strArticleList = json_encode($collect_info, JSON_UNESCAPED_UNICODE);
         $stmt = PDO_Execute($query,array( $strArticleList ,$key));
         if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
             $error = PDO_ErrorInfo();
             $respond['status']=1;
             $respond['message']=$error[2];
         }
     }

}

	echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>