<?php
//查询term字典

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';

$output  = array();
if(isset($_GET["id"])){
    PDO_Connect(""._FILE_DB_USER_ARTICLE_);
    $article_id=$_GET["id"];
    $query = "SELECT collect_id as id from article_list  where article_id = ?  ";
    $exist = PDO_FetchAll($query,array($article_id));
    $exist_id = array();
    for ($i=0; $i < count($exist) ; $i++) { 
        # query collect title
        $query = "SELECT title from collect  where id = ?  ";
        $exist[$i]["title"] = PDO_FetchOne($query,array($exist[$i]["id"]));        
        $exist_id[$exist[$i]["id"]] = 1;
    }
    $output["exist"] = $exist;

    $query = "SELECT id,title from collect  where owner = ? AND status <> 0 order by modify_time DESC limit 0,15";
    $others = PDO_FetchAll($query,array($_COOKIE["userid"])); 
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