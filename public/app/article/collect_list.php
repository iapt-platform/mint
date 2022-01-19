<?php
#某用户的文章列表

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';


if(isset($_GET["userid"])){
    PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
    $userid=$_GET["userid"];
    $query = "SELECT uid as id,title,subtitle,summary,article_list,owner,status,lang,create_time,modify_time from "._TABLE_COLLECTION_."  where owner = ? and status <> 0 order by modify_time DESC";
    $Fetch = PDO_FetchAll($query,array($userid));
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}else{
	echo json_encode(array(), JSON_UNESCAPED_UNICODE);	
}



?>