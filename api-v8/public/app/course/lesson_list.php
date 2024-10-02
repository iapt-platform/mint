<?php
//

require_once "../config.php";
require_once "../public/_pdo.php";

global $PDO;
PDO_Connect(""._FILE_DB_COURSE_);
$query = "SELECT * from lesson where course_id = ? order by date DESC    limit 0,200";
$fAllLesson = PDO_FetchAll($query,array($_GET["id"]));
echo json_encode($fAllLesson, JSON_UNESCAPED_UNICODE);

?>