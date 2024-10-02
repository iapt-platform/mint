<?php
/*
更新用户字典，已经不使用了
 */
include "./_pdo.php";
include "config.php";

$serverError = "";
$serverReturn = "";
$serverOp = "";

$input = file_get_contents("php://input");

$xml = simplexml_load_string($input);
$db_file = $dir_dict_user . $file_dict_user_default;
PDO_Connect("$db_file");

$wordsList = $xml->xpath('//word');
$recorderCount = 0;
/**/
foreach ($wordsList as $ws) {
    $id = $ws->id;
    if ($id == "0") {
        //new recorder
        $query = "INSERT INTO dict ('id','pali','type','gramma','parent','mean','detail','factors','factormean','confer','class','lock','tag') VALUES (null,?,?,?,?,?,?,?,?,?,?,?,?)";
        $params = array($ws->pali, $ws->type, $ws->gramma, $ws->parent, $ws->mean, $ws->note, $ws->factors, $ws->fm, $ws->confer, $ws->status, $ws->lock, $ws->tag);
        $stmt = @PDO_Execute($query, $params);
        $last_id = $PDO->lastInsertId();
        $serverOp = "insert";
        $serverReturn = $last_id;
    } else {
        $query = "UPDATE dict SET type = ? ,gramma = ? ,parent = ? ,mean = ? ,detail = ? ,factors = ? ,factormean = ? ,confer = ? ,class = ? ,lock = ? ,tag = ?  WHERE id = ?";
        $params = array($ws->type, $ws->gramma, $ws->parent, $ws->mean, $ws->note, $ws->factors, $ws->fm, $ws->confer, $ws->status, $ws->lock, $ws->tag, $id);
        $stmt = @PDO_Execute($query, $params);
        $serverOp = "update";
        $serverReturn = $id;
    }
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $serverError .= "error - $error[2]";
        $serverReturn = -1;
    } else {
        $recorderCount++;
    }
    $output = '{ "sever_op":"insert" , "server_return":2345 , , "server_error":"haha"}';
    $output = '{"msg":[' . '{"server_op":"' . $serverOp . '","server_return":' . $serverReturn . ',"server_error":"' . $serverError . '" }]}';
    echo $output;
}
