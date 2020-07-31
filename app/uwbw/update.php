<?php
/*
get xml doc from db
*/
include "../path.php";
include "../public/_pdo.php";
include "../public/function.php";


$aData=json_decode($_POST["data"]);

PDO_Connect("sqlite:"._FILE_DB_USER_WBW_);

/* 开始一个事务，关闭自动提交 */
$PDO->beginTransaction();
$query="UPDATE wbw SET data= ?  , receive_time= ?  , modify_time= ?   where block_id= ?  and wid= ?  ";
$sth = $PDO->prepare($query);

foreach ($aData as $data) {
    $sth->execute(array($data->data,mTime(),$data->time,$data->block_id,$data->word_id));
}
$PDO->commit();

$respond=array("status"=>0,"message"=>"");

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
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>