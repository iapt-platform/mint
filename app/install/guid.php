<?php
if(isset($_GET["num"])){
	$count=$_GET["num"];
}
else{
	$count=1;
}
for($i=0;$i<$count;$i++){
 echo com_create_guid(). "<br>";
}
?>