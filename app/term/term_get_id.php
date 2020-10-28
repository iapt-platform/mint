<?php
/*
查询term字典
输入单词列表
输出查到的结果
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';

PDO_Connect("sqlite:"._FILE_DB_TERM_);

$fetch = array();
if(isset($_POST["id"])){
        $query = "SELECT * FROM term WHERE guid = ? ";

        $fetch = PDO_FetchRow($query,array($_POST["id"]));
        $userinfo = new UserInfo();
        if($fetch){
            # code...
            $fetch["user"]=$userinfo->getName($fetch["owner"]);
        }
    }

echo json_encode($fetch, JSON_UNESCAPED_UNICODE);
?>