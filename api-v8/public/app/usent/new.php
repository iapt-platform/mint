<?php
/*
get xml doc from db
 */
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$aData = json_decode($_POST["data"]);

PDO_Connect(_FILE_DB_SENTENCE_,_DB_USERNAME_,_DB_PASSWORD_);

/* 开始一个事务，关闭自动提交 */
$PDO->beginTransaction();
$query = "INSERT INTO "._TABLE_SENTENCE_." ( uid , block_uid , book_id , paragraph , word_start , word_end ,  author , editor_uid , content , language ,  modify_time ,  updated_at) VALUES (?,?,?,?,?,?,?,?,?,?,?,now())";

$sth = $PDO->prepare($query);

foreach ($aData as $data) {
    $sth->execute(
        array($data->id,
            $data->blockid,
            $data->book,
            $data->paragraph,
            $data->begin,
            $data->end,
            $data->author,
            $data->editor,
            $data->text,
            $data->lang,
            mTime()
        ));
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
