<?php
//添加文章到文集
# TODO 需要判断文集的修改权限
# TODO children >0 不能删除
# TODO 因为要保证顺序不变，已经存在的不能删除
require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
require_once __DIR__."/../public/snowflakeid.php";
$snowflake = new SnowFlakeId();

$output  = array();
$respond = array();
$respond['status']=0;
$respond['message']="";
$respond['id']=$_POST["id"];

if(isset($_POST["id"])){
    $dirty_collect = array();
    $data = json_decode($_POST["data"]);
    $title = $_POST["title"];
    PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
    $article_id=$_POST["id"];
    //找出脏的collect
    $query = "SELECT collect_id FROM "._TABLE_ARTICLE_COLLECTION_."  WHERE article_id = ? ";
    $collect = PDO_FetchAll($query,array($article_id));
    foreach ($collect as $key => $value) {
        # code...
        $dirty_collect[$value["collect_id"]] = 1;
    }
    $query = "DELETE FROM "._TABLE_ARTICLE_COLLECTION_." WHERE article_id = ? ";
     PDO_Execute($query,array($article_id));
     if(count($data)>0){
        /* 开始一个事务，关闭自动提交 */
        $PDO->beginTransaction();
        $query = "INSERT INTO "._TABLE_ARTICLE_COLLECTION_." 
                    (
                        id,
                        collect_id, 
                        article_id,
                        level,
                        title
                    ) VALUES (? , ?, ?, ? , ?)";
        $sth = $PDO->prepare($query);
        foreach ($data as $row) {
            $sth->execute(array(
                        $snowflake->id() , 
                        $row,
                        $article_id,
                        1,
                        $title
                        ));
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
     $query = "SELECT collect_id FROM "._TABLE_ARTICLE_COLLECTION_." WHERE article_id  = ?";
     $collect = PDO_FetchAll($query,array($article_id));
     foreach ($collect as $key => $value) {
        # code...
        $dirty_collect[$value["collect_id"]] = 1;
    }
     foreach ($dirty_collect as $key => $value) {
         # code...
         $query = "SELECT level,article_id as article , title FROM "._TABLE_ARTICLE_COLLECTION_." WHERE collect_id  = ? order by id ASC";
         $collect_info = PDO_FetchAll($query,array($key));
         $query = "UPDATE "._TABLE_COLLECTION_." SET article_list = ? WHERE uid = ? ";
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