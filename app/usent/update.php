<?php
/*
get xml doc from db
*/
include "../path.php";
include "../public/_pdo.php";
include "../public/function.php";


$aData=json_decode($_POST["data"],TRUE);

PDO_Connect("sqlite:"._FILE_DB_SENTENCE_);

//查询没有id的哪些是数据库里已经存在的，防止多次提交同一条记录造成一个句子 多个channal
$newList = array();
$oldList = array();
$query = "SELECT id FROM sentence WHERE book = ? and paragraph = ? and  begin = ? and end = ? and channal = ? limit 0 , 1 ";
foreach ($aData as $data) {
	if(!isset($data["id"]) || empty($data["id"])){
		$id = PDO_FetchOne($query,array($data["book"],
																	   $data["paragraph"],
																	   $data["begin"],
																	   $data["end"],
																	   $data["channal"]
																	   ));
		if(empty($id)){
			$newList[] = $data;
		}
		else{
			$data["id"] = $id;
			$oldList[] = $data;
		}
	}
	else{
		$oldList[] = $data;
	}
}

/* 修改现有数据 */
$PDO->beginTransaction();
$query="UPDATE sentence SET text= ?  , status = ? , receive_time= ?  , modify_time= ?   where  id= ?  ";
$sth = $PDO->prepare($query);

foreach ($oldList as $data) {
	if(isset($data["id"])){
		if(isset($data["time"])){
			$modify_time = $data["time"];
		}
		else{
			$modify_time = mTime();
		}
		$sth->execute(array($data["text"], $data["status"], mTime(),$modify_time,$data["id"]));
	} 
}

$PDO->commit();

$respond=array("status"=>0,"message"=>"","insert_error"=>"","new_list"=>array());

if (!$sth || ($sth && $sth->errorCode() != 0)) {
	/*  识别错误且回滚更改  */
	$PDO->rollBack();
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
	$respond['status']=0;
	$respond['message']="成功";
}


/* 插入数据 */
$PDO->beginTransaction();
$query = "INSERT INTO sentence (id, 
														parent,
														book,
														paragraph,
														begin,
														end,
														channal,
														tag,
														author,
														editor,
														text,
														language,
														ver,
														status,
														modify_time,
														receive_time
														) 
										VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
$sth = $PDO->prepare($query);
$new_id = array();
foreach ($newList as $data) {
		$uuid = UUID::v4();
		if($data["parent"]){
			$parent = $data["parent"];
		}
		else{
			$parent  = "";
		}
		$sth->execute(array($uuid,
										  $parent,
										  $data["book"], 
										  $data["paragraph"], 
										  $data["begin"], 
										  $data["end"], 
										  $data["channal"], 
										  $data["tag"], 
										  $data["author"], 
										  $_COOKIE["userid"],
										  $data["text"],
										  $data["language"],
										  1,
										  7,
										  mTime(),
										  mTime()
										));
		$new_id[] = array($uuid,$data["book"],$data["paragraph"],$data["begin"],$data["end"],$data["channal"]);
}
$PDO->commit();


if (!$sth || ($sth && $sth->errorCode() != 0)) {
	/*  识别错误且回滚更改  */
	$PDO->rollBack();
	$error = PDO_ErrorInfo();
	$respond['insert_error']=$error[2];
	$respond['new_list']=array();
}
else{
	$respond['insert_error']=0;
	$respond['new_list']=$new_id;
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>