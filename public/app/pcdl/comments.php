<?php
include "../config.php";
include "./_pdo.php";
if (isset($_POST["album"])) {
    $album = $_POST["album"];
} else {
    echo "no album id";
    exit;
}

if (isset($_POST["book"])) {
    $book = $_POST["book"];
} else {
    echo "no book id";
    exit;
}

if (isset($_POST["paragraph"])) {
    $paragraph = $_POST["paragraph"];
} else {
    echo "no paragraph id";
    exit;
}

if (isset($_POST["text"])) {
    $text = $_POST["text"];
} else {
    echo "no text";
    exit;
}
$db_file = _FILE_DB_COMMENTS_;
PDO_Connect("$db_file");

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO comments ('id','album','book','paragraph','text','user','time') VALUES (NULL,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);

$newData = array($album, $book, $paragraph, $text, 4, time());
$stmt->execute($newData);

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {
    echo "updata 1 recorders.";
}
