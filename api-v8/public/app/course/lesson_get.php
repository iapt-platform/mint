<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../ucenter/function.php";

$userinfo = new UserInfo();

PDO_Connect(""._FILE_DB_COURSE_);
$query = "SELECT * from lesson where id = ?   limit 0,1";
$fLesson = PDO_FetchRow($query,array($_GET["id"]));

if ($fLesson) {
    # code...
    $user = $userinfo->getName($fLesson["teacher"]);
	$fLesson["teacher_info"] = $user;
	echo json_encode($fLesson, JSON_UNESCAPED_UNICODE);
}
else{
	echo json_encode(array(), JSON_UNESCAPED_UNICODE);
}


?>