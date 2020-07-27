<?php
include "./config.php";
//获取服务器端文件列表
$dir= $dir_myPaliCannon.$_GET["folder"]."/";
$files = scandir($dir);
$arrlength=count($files);

for($x=0;$x<$arrlength;$x++) {
	if(is_file($dir.$files[$x])){
		echo $files[$x].',';
	}
}
?>