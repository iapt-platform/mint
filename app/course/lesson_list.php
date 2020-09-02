<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);
$query = "select * from lesson where course_id = '{$_GET["id"]}'   limit 0,100";
$fAllLesson = PDO_FetchAll($query);
echo json_encode($fAllLesson, JSON_UNESCAPED_UNICODE);

?>