<?php
require_once __DIR__.'/../config.php';

/*
load language file
范例
echo $_local->gui->XXXX;
<?php echo $_local->gui->XXXX;?>
gLocal.gui.XXXX
*/
global $_local;
global $_local_arr;

if(isset($_GET["language"])){
	$currLanguage=$_GET["language"];
}
else{
	if(isset($_COOKIE["language"])){
		$currLanguage=$_COOKIE["language"];
	}
	else{
		$currLanguage="en";
	}
}
setcookie("language", $currLanguage, time()+60*60*24*365,"/");
if(file_exists(_DIR_LANGUAGE_.'/'.$currLanguage.".json")){
	$_local=json_decode(file_get_contents(_DIR_LANGUAGE_.'/'.$currLanguage.".json"));
	$_local_arr=json_decode(file_get_contents(_DIR_LANGUAGE_.'/'.$currLanguage.".json"),true);
}
else{
	$_local=json_decode(file_get_contents(_DIR_LANGUAGE_."/default.json"));
	$_local_arr=json_decode(file_get_contents(_DIR_LANGUAGE_."/default.json"),true);
}
?>