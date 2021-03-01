<?php
//文章列表

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';
PDO_Connect(""._FILE_DB_USER_ARTICLE_);
if(isset($_GET["userid"])){
    $userid=$_GET["userid"];
    $query = "SELECT * from article  where owner = ? and status <> 0 order by modify_time DESC";
    $Fetch = PDO_FetchAll($query,array($userid));
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
else{

}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>