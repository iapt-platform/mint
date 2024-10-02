<?php
//文章列表

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
if(isset($_GET["userid"])){
    $userid=$_GET["userid"];
    $query = "SELECT uid as id , title,subtitle ,summary,content,owner,setting,status,lang,create_time,modify_time from "._TABLE_ARTICLE_."  where owner = ? and status <> 0 order by modify_time DESC";
    $Fetch = PDO_FetchAll($query,array($userid));
    if($Fetch){
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>