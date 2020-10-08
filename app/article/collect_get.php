<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';


if(isset($_GET["id"])){
    PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
    $id=$_GET["id"];
    $query = "select * from collect  where id = ".$PDO->quote($id);
    $Fetch = PDO_FetchRow($query);
    if($Fetch){
        $userinfo = new UserInfo();
        $user = $userinfo->getName($Fetch["owner"]);
        $Fetch["username"] = $user;
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
    }
}
else if(isset($_GET["article"])){
    PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
    $article=$_GET["article"];
    $query = "select * from collect  where article_list like ".$PDO->quote('%'.$article.'%');
    $Fetch = PDO_FetchAll($query);

        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;

}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>