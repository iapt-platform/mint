<?php
include "../config.php";
include "../public/_pdo.php";

if (isset($_POST['dict_id'])) {
    $dict_id = $_POST['dict_id'];
} else {
    $dict_id = -1;
}
if (isset($_POST['word_id'])) {
    $word_id = $_POST['word_id'];
} else {
    $word_id = -1;
}
if (isset($_POST['data'])) {
    $data = $_POST['data'];
} else {
    $data = "";
}
if (isset($_POST['word_status'])) {
    $status = $_POST['word_status'];
} else {
    $status = "1";
}

$dictFileName = _FILE_DB_REF_;
PDO_Connect("$dictFileName");
$query = "update dict set status='$status' where id='$word_id'";
$stmt = @PDO_Execute($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    print_r($error[2]);
}

$dictFileName = $dir_dict_3rd . "all.db3";
PDO_Connect("$dictFileName");

$query = "DELETE FROM dict WHERE \"from\" = '$word_id' ";
$stmt = @PDO_Execute($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    print_r($error[2]);
} else {

}
$word_data = json_decode($data);
//print_r($word_data);
//echo "pali:".$word_data[0]->pali."<br>";
// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO dict ('id','pali','type','gramma','parent','mean','note','factors','factormean','dict_id','from')
	        VALUES (NULL,?,?,?,?,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);

foreach ($word_data as $value) {
    $newData = array($value->pali,
        $value->type,
        $value->gramma,
        $value->parent,
        $value->mean,
        $value->note,
        $value->factor,
        $value->factor_mean,
        $dict_id,
        $word_id);
    $stmt->execute($newData);
}

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {
    echo "成功提交" . count($word_data) . "条数据。";
}
