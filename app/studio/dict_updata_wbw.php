<?php
require 'checklogin.inc';
include "../public/_pdo.php";
include "../public/config.php";

$input = file_get_contents("php://input");

$return="";
$serverMsg="";

$xml = simplexml_load_string($input);

$db_file = $_file_db_wbw;
PDO_Connect("sqlite:$db_file");

$wordsList = $xml->xpath('//word');
//$serverMsg+= "word count:".count($wordsList)."<br>";

//remove the same word
foreach($wordsList as $ws){
	$combine=$ws->pali.$ws->guid.$ws->type.$ws->gramma.$ws->parent.$ws->parent_id.$ws->mean.$ws->factors.$ws->fm.$ws->part_id;
	$word[$combine]=$ws;
}

$arrInserString=array();
$arrExistWords=array();

foreach($word as $x=>$ws){
	$query = "select id,ref_counter  from dict where 
				\"guid\"=".$PDO->quote($ws->guid)." AND 
				\"pali\"=".$PDO->quote($ws->pali)." AND 
				\"type\"=".$PDO->quote($ws->type)." AND 
				\"gramma\"=".$PDO->quote($ws->gramma)." AND 
				\"mean\"=".$PDO->quote($ws->mean)." AND 
				\"parent\"=".$PDO->quote($ws->parent)." AND 
				\"parent_id\"=".$PDO->quote($ws->parent_id)." AND 
				\"factors\"=".$PDO->quote($ws->factors)." AND 
				\"factormean\"=".$PDO->quote($ws->fm)." AND 
				\"part_id\"=".$PDO->quote($ws->part_id);
	$Fetch = PDO_FetchAll($query);
	$FetchNum = count($Fetch);
	
	if($FetchNum==0){
		//new recorder
		$params=array($ws->guid,
					  $ws->pali,
					  $ws->type,
					  $ws->gramma,
					  $ws->parent,
					  $ws->parent_id,
					  $ws->mean,
					  $ws->note,
					  $ws->factors,
					  $ws->fm,
					  $ws->part_id,
					  $ws->status,
					  $ws->language,
					  $UID,
					  time());
		array_push($arrInserString,$params);
		
	}
	else{
		// "have a same recorder";
		$wordId=$Fetch[0]["id"];
		$ref=$Fetch[0]["ref_counter"]+1;
		//更新引用计数
		$query="UPDATE dict SET ref_counter='$ref' where id=".$PDO->quote($wordId);
		$stmt = @PDO_Execute($query);
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			$error = PDO_ErrorInfo();
			echo "error".$error[2]."<br>";
		}	
		//去掉已经有的索引
		$query = "select count(*)  from user_index where word_index={$wordId} and user_id={$UID}";
		$num = PDO_FetchOne($query);
		if($num==0){
			array_push($arrExistWords,$Fetch[0]["id"]);
		}
	}
}
/* 开始一个事务，关闭自动提交 */
$PDO->beginTransaction();
$query="INSERT INTO dict ('id',
						  'guid',
						  'pali',
						  'type',
						  'gramma',
						  'parent',
						  'parent_id',
						  'mean',
						  'note',
						  'factors',
						  'factormean',
						  'part_id',
						  'status',
						  'dict_name',
						  'language',
						  'creator',
						  'time') 
				   VALUES (null,?,?,?,?,?,?,?,?,?,?,?,?,'user',?,?,?)";
$stmt = $PDO->prepare($query);
foreach($arrInserString as $oneParam){
	$stmt->execute($oneParam);
}
/* 提交更改 */
$PDO->commit();
$lastid=$PDO->lastInsertId();

if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	echo "error - $error[2] <br>";
}
else{
	
	$count=count($arrInserString);
	echo "updata $count recorders.";
	//更新索引表
	
	$iFirst=$lastid-$count+1;
	for($i=0;$i<$count;$i++){
		array_push($arrExistWords,$iFirst+$i);
	}
	if(count($arrExistWords)>0){
		/* 开始一个事务，关闭自动提交 */
		$PDO->beginTransaction();
		$query="INSERT INTO user_index ('id','word_index','user_id','create_time') 
								VALUES (null,?,{$UID},?)";
		$stmt = $PDO->prepare($query);
		foreach($arrExistWords as $oneId){
			$stmt->execute(array($oneId,time()));
		}
		/* 提交更改 */
		$PDO->commit();
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			$error = PDO_ErrorInfo();
			echo "error - $error[2] <br>";
		}
		else{
			echo "updata index ".count($arrExistWords)." recorders.";
		}
	}
	else{
		echo "updata index 0";
	}
}
		
?>