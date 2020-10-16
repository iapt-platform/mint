<?php
//查询term字典

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../ucenter/function.php';


if(isset($_GET["id"])){
    PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
    $id=$_GET["id"];
    $query = "SELECT * FROM channal  WHERE id = ? ";
    $Fetch = PDO_FetchRow($query,array($id));
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}
else{
    PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
    $id=$_GET["id"];
    $query = "SELECT * FROM channal  WHERE owner = ? ";
    $Fetch = PDO_FetchAll($query,array($_COOKIE["userid"]));
    echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
}


?>