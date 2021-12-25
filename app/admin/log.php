<?php
require_once "../config.php";

function mylog($message){
	$fpLog = fopen(_DIR_LOG_APP_,"a");
	if($fpLog){
		fputs($fpLog,array(date("Y/m/d H:m:s") ,__FILE__,__LINE__,$message));
	}
}

?>