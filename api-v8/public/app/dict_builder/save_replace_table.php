<?php
include "./config.php";
include "./_pdo.php";

if(isset($_POST['dict_id'])){
	$dict_id=$_POST['dict_id'];
}
else{
	$dict_id="";
}
if(isset($_POST['main'])){
	$data_main=$_POST['main'];
}
else{
	$data_main="";
}
if(isset($_POST['pali'])){
	$data_pali=$_POST['pali'];
}
else{
	$data_pali="";
}
if(isset($_POST['part'])){
	$data_part=$_POST['part'];
}
else{
	$data_part="";
}
if(isset($_POST['type'])){
	$data_type=$_POST['type'];
}
else{
	$data_type="";
}

$filename="replace_table/$dict_id"."_main.txt";
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $data_main);
echo "main.txt 保存成功<br />";
fclose($myfile);

$filename="replace_table/$dict_id"."_pali.txt";
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $data_pali);
echo "pali.txt 保存成功<br />";
fclose($myfile);

$filename="replace_table/$dict_id"."_part.txt";
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $data_part);
echo "part.txt 保存成功<br />";
fclose($myfile);

$filename="replace_table/$dict_id"."_type.txt";
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $data_type);
echo "type.txt 保存成功<br />";
fclose($myfile);

?>