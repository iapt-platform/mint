<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
</head>
<body>
<form action="guid.php" method="get">
GUID Number: <input type="text" name="num" value="1" /><br>
<input type="submit">
</form>
<?php
if(isset($_GET["num"])){
	$count=$_GET["num"];
	echo "<h2>$count - GUID</h2>";
	echo "<textarea rows=\"20\" cols=\"100\">";
	for($i=0;$i<$count;$i++){
	 echo com_create_guid(). "\r\n";
	}
	echo "</textarea>";	
}

?>
</body>
</html>