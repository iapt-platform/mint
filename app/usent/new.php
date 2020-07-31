<?php
/*
get xml doc from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";


$aData=json_decode($_POST["data"]);

PDO_Connect("sqlite:"._FILE_DB_SENTENCE_);

/* 开始一个事务，关闭自动提交 */
$PDO->beginTransaction();
$query="INSERT INTO sentence ('id','block_id','book','paragraph','begin','end','tag','author','editor','text','language','ver','status','modify_time','receive_time') VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";

$sth = $PDO->prepare($query);

foreach ($aData as $data) {
    $sth->execute(
    	       array($data->id,
    	       	     $data->blockid,
    	       	     $data->book,
    	       	     $data->paragraph,
    	       	     $data->begin,
    	       	     $data->end,
    	       	     $data->tag,
    	       	     $data->author,
    	       	     $data->editor,
    	       	     $data->text,
    	       	     $data->lang,
    	       	     1,
    	       	     1,
    	       	     mTime(),
    	       	     mTime()
    	       	 ));
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