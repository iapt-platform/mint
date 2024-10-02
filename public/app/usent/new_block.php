<?php
/*
新建译文段落块，已经废弃
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$aData = json_decode($_POST["data"]);

PDO_Connect( _FILE_DB_SENTENCE_,_DB_USERNAME_, _DB_PASSWORD_);

/* 开始一个事务，关闭自动提交 */
$PDO->beginTransaction();
$query = "INSERT INTO sent_block ('id','book','paragraph','owner','lang','author','editor','tag','modify_time','receive_time') VALUES (?,?,?,?,?,?,?,?,?,?)";

$sth = $PDO->prepare($query);

foreach ($aData as $data) {
    if (isset($_COOKIE["userid"])) {
        $userid = $_COOKIE["userid"];
    } else {
        $userid = $data->userid;
    }
    $sth->execute(array($data->id, $data->book, $data->paragraph, $userid, $data->lang, $data->author, $data->editor, $data->tag, $data->time, mTime()));
}
$PDO->commit();

$respond = array("status" => 0, "message" => "");

if (!$sth || ($sth && $sth->errorCode() != 0)) {
    /*  识别错误且回滚更改  */
    $PDO->rollBack();
    $error = PDO_ErrorInfo();
    $respond['status'] = 1;
    $respond['message'] = $error[2];

} else {
    $respond['status'] = 0;
    $respond['message'] = "成功";
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
