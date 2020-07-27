<?php
require 'checklogin.inc';
include "./config.php";
$op=_POST["op"];
$file=$dir_user_base.$userid.$dir_myApp."setup.json";
switch($op){
	case "load":
	echo file_get_contents($file);
	break;
	case "save":
	$key=_POST["key"];
	$value=_POST["value"];
	$setup=json_decode(file_get_contents($file));
	$setup[$key]=$value;
	$strSetup=json_encode($setup, JSON_UNESCAPED_UNICODE);
	file_put_contents($file,$strSetup);
	echo $strSetup;
	break;
}

?>