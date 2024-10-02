<?php
require 'checklogin.inc';

//获取服务器端字典列表
/*
$dir = 'dict/';
$files = scandir($dir);
$arrlength=count($files);

$out="";
for($x=0;$x<$arrlength;$x++) {
	if(is_file($dir.$files[$x])){
		$out=$out.','.$files[$x];
	}
}
$out = ltrim($out,",");
echo $out;
*/

//echo file_get_contents($dir_user_base.$userid.$dir_myApp."/dictlist.json");
echo "var local_dict_list=[];";
?>