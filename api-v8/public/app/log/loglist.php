<!DOCTYPE HTML>
<html>
<head>
</head>
<body>
<ol>
<?php
require_once("../config.php");
$scan = scandir(_DIR_LOG_);
$fileCounter = 0;

foreach($scan as $filename) {
	if (is_file(_DIR_LOG_."/".$filename)){
		if(substr($filename,0,5)=='pref_'){
			$date = substr($filename,5,-4);
			echo "<li><a href='showlog.php?file={$filename}' target='log'>".$date.'</a></li>';
		}
	}
}
?>
</ol>
</body>
</html>
