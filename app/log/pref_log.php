<?php
require_once("../path.php");
$logstart = microtime(true)*1000;
$strstart = date("h:i:sa");
function PrefLog(){
	$file = fopen(_DIR_LOG_."/pref_".date("Y-m-d").".log","a");
	if($file){
		fputcsv($file,[$_SERVER['PHP_SELF'],$GLOBALS['strstart'],sprintf("%d",microtime(true)*1000-$GLOBALS['logstart']),$_SERVER['REMOTE_ADDR']]);
		fclose($file);
	}
}


?>