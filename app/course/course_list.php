<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../media/function.php';
require_once '../ucenter/function.php';


/*
状态

0 删除
10 私有
20 不公开列出
30 公开连载
40 已完结
*/
global $PDO;
PDO_Connect(""._FILE_DB_COURSE_);

if(isset($_GET["teacher"])){
    $query = "SELECT * from course where teacher = ?  order by create_time DESC limit 0,100";
    $Fetch = PDO_FetchAll($query,array($_GET["teacher"]));    
}
else{
    $query = "SELECT * from course where 1  order by create_time DESC limit 0,100";
    $Fetch = PDO_FetchAll($query);
}
$userinfo = new UserInfo();

foreach ($Fetch as $key => $value) {
    # code...
    $user = $userinfo->getName($value["teacher"]);
    $Fetch[$key]["teacher_info"] = $user;
}

echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

?>