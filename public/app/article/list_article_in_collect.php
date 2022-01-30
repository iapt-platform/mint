<?php
//查询某article 在哪几个collection里面出现

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

$output  = array();
if(isset($_GET["id"])){
    PDO_Connect(_FILE_DB_USER_ARTICLE_,_DB_USERNAME_,_DB_PASSWORD_);
    $article_id=$_GET["id"];
    $query = "SELECT collect_id as id from "._TABLE_ARTICLE_COLLECTION_."  where article_id = ?  ";
    $exist = PDO_FetchAll($query,array($article_id));
    $exist_id = array();
	#查询已经存在的collection详细信息
    for ($i=0; $i < count($exist) ; $i++) { 
        # query collect title
        $query = "SELECT title from "._TABLE_COLLECTION_."  where uid = ?  ";
        $exist[$i]["title"] = PDO_FetchOne($query,array($exist[$i]["id"]));        
        $exist_id[$exist[$i]["id"]] = 1;
    }
    $output["exist"] = $exist;

	#查询所有的
    $query = "SELECT uid as id,title from "._TABLE_COLLECTION_."  where owner = ? AND status <> 0 order by modify_time DESC limit 50";
    $others = PDO_FetchAll($query,array($_COOKIE["user_uid"])); 
    foreach ($others as $key => $value) {
        # remove exist record
        if(!isset($exist_id[$value["id"]])){
            $output["others"][] = $value;
        }
    }
    $output["article_id"]=$article_id;
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
}
else{
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);	
}

?>