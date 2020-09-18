<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";

global $PDO;
PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
$query = "select * from channal where owner = '{$_COOKIE["userid"]}'   limit 0,100";
$Fetch = PDO_FetchAll($query);
echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);

?>				