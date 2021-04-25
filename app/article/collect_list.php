<?php
#某用户的文章列表

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';


if(isset($_GET["userid"])){
    PDO_Connect(""._FILE_DB_USER_ARTICLE_);
    $userid=$_GET["userid"];
    $query = "SELECT * from collect  where owner = ? and status <> 0 order by modify_time DESC";
    $Fetch = PDO_FetchAll($query,array($userid));
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
    exit;
}

echo json_encode(array(), JSON_UNESCAPED_UNICODE);	

?>