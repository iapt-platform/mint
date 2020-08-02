<?php
require_once "../path.php";

$filename = _DIR_USERS_GUIDE_."/en/".$_GET["id"].".md";
$output["data"]  =  file_get_contents($filename) ;
$output["id"]  =$_GET["id"];
echo json_encode($output,JSON_UNESCAPED_UNICODE);

?>