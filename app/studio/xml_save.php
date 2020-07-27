<?php 
$input = file_get_contents("php://input"); //

$strHead = mb_strstr($input,'#',true,"UTF-8");
$xmlBody = strstr($input,'#');
$xmlBody = substr($xmlBody,1);
parse_str($strHead);//
$histroyPath = $filename."_histroy";
if(is_dir($histroyPath)==FALSE){
	if (!mkdir($histroyPath)) {
		die('Failed to create folders...');
	}
}
$datatime=date("Ymd").date("His");
$purefilename = basename($filename);
$histroyFilename = $histroyPath.'/'.$datatime.'_'.$purefilename;

//save histroy file
$myfile = fopen($histroyFilename, "w") or die("Unable to open file!");
fwrite($myfile, $xmlBody);
fclose($myfile);

//save data file
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $xmlBody);
fclose($myfile);

//delete spare histroy file 
//
$dir    = $histroyPath.'/';
$files = scandir($dir);
$arrlength=count($files);
if($arrlength>7){
	$del = $arrlength-7;
	$delcount=0;
	for($x=2;$x<$del+2;$x++) {
		if(is_file($dir.$files[$x])){
			unlink($dir.$files[$x]);
		}
	}
}

echo( $module_gui_str['editor']['1015']);
?>

