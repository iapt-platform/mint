<?php
require_once '../config.php';
require_once '../redis/function.php';
require_once '../dict/function.php';

$output=json_encode(array(), JSON_UNESCAPED_UNICODE);

if(isset($_GET["word"])){
	$inputWord = $_GET["word"];
}
else{
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}
$redis = redis_connect();

if($redis!==false){
	#如果没有查巴缅替换拆分
	if($redis->hExists("dict://comp",$inputWord)===TRUE){
		$output = $redis->hGet("dict://comp",$inputWord) ;
		if(empty($output)){
			echo json_encode(array(), JSON_UNESCAPED_UNICODE);
		}
		else{
			echo $output;
		}
	}
	else{
		echo json_encode(array(), JSON_UNESCAPED_UNICODE);
	}
}
else{
	$dbh = new PDO(_DICT_DB_COMP_, "", "", array(PDO::ATTR_PERSISTENT => true));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	$query = "SELECT parts from "._TABLE_DICT_COMP_." where pali =?";
	$stmt = $dbh->prepare($query);
	$stmt->execute(array($inputWord));
	$fComp = $stmt->fetchAll(PDO::FETCH_ASSOC);		
	$output = json_encode($fComp, JSON_UNESCAPED_UNICODE);
	echo $output;	
}


?>