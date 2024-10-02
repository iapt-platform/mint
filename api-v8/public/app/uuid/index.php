<?php
include "../public/function.php";

$num = $_GET["num"];
if($num>10000){
	$num=10000;
}
for($i=0;$i<$num;$i++){
	echo UUID::v4()."<br>";
}

?>