<?php
/*
get user sentence from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";



$dns = "sqlite:"._FILE_DB_PALI_SENTENCE_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$query="SELECT book,paragraph, begin,end ,text FROM pali_sent WHERE 1 ";
$stmt = $dbh->prepare($query);
$stmt->execute();
$redis = new redis();  
$r_conn = $redis->connect('127.0.0.1', 6379);  
$stringSize = 0;
if($r_conn){
	while($sent = $stmt->fetch(PDO::FETCH_ASSOC)){
		$stringSize += strlen($sent["text"]);
		if($stringSize>50000000){
			sleep(1);
			$stringSize=0;
			echo $sent["book"]."_".$sent["paragraph"]."\n";
		}
		$result = $redis->set('pali_sent_'.$sent["book"]."_".$sent["paragraph"]."_".$sent["begin"]."_".$sent["end"],$sent["text"]);  
	}
	echo "完成";
}
else{
	echo "连接redis失败";
}

?>