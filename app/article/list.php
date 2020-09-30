<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';


if(isset($_GET["userid"])){
    PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
    $userid=$_GET["userid"];
    $query = "SELECT * from article  where owner = ".$PDO->quote($userid)." and status <> 0 order by modify_time DESC";
    $Fetch = PDO_FetchAll($query);
    if($Fetch){
        /*
        $userinfo = new UserInfo();
        $user = $userinfo->getName($Fetch["owner"]);
        $Fetch["username"] = $user;
        */
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        exit;
    }
}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>