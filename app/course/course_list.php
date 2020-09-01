<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/load_lang.php';
require_once '../media/function.php';

if(isset($_GET["teacher"])){
    $teacher = " teacher = '".$_GET["teacher"]."'";
}
else{
    $teacher = " 1= 1";
}

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_COURSE_);

$query = "select * from course where $teacher  order by create_time DESC limit 0,100";
$Fetch = PDO_FetchAll($query);
echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

?>