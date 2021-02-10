<?php 
//统计用户经验值
require_once '../path.php';
require_once "../public/function.php";

$output=array();
if(isset($_GET["userid"])){
	$userid = $_GET["userid"];
}
else if(isset($_COOKIE["userid"])){
	$userid = $_COOKIE["userid"];
}
else{
	exit;
}
if(isset($userid)){
	$dns = "sqlite:"._FILE_DB_USER_ACTIVE_;
	$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$query = "SELECT date,duration,hit  FROM active_index WHERE user_id = ? ";
	$sth = $dbh->prepare($query);
	$sth->execute(array($userid));
	$last = 0;
	while($row = $sth->fetch(PDO::FETCH_ASSOC)){
		$curr = $last+$row["duration"]/3600000;
		$output[]=array($row["date"],number_format($last,3,".",""),number_format($curr,3,".",""),number_format($last,3,".",""),number_format($curr,3,".",""),$row["hit"]);
		$last = $curr;
	}

	$json =  json_encode($output);
	echo str_replace('"','',$json);
}
?>