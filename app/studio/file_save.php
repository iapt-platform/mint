<?php 
include("config.php");

$filename=$_POST["filename"];
$data=$_POST["data"];

//save data file
$myfile = fopen($filename, "w") or die("Unable to open file!");
fwrite($myfile, $data);
fclose($myfile);

echo("Successful");
?>

