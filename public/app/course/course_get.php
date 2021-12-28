<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../ucenter/function.php";

$userinfo = new UserInfo();
PDO_Connect(_FILE_DB_COURSE_);
$query = "SELECT * from "._TABLE_COURSE_." where id = ? ";
$fCourse = PDO_FetchRow($query,array($_GET["id"]));

if ($fCourse) {
    # code...
    $user = $userinfo->getName($fCourse["teacher"]);
	$fCourse["teacher_info"] = $user;
	echo json_encode($fCourse, JSON_UNESCAPED_UNICODE);
}
else{
	echo json_encode(array(), JSON_UNESCAPED_UNICODE);
}


?>