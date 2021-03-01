<?php
include "./config.php";
include "./_pdo.php";

if (isset($_POST["title"])) {
    $title = $_POST["title"];
} else {
    echo "no title";
    exit;
}

if (isset($_POST["summary"])) {
    $summary = $_POST["summary"];
} else {
    echo "no summary";
    exit;
}

if (isset($_POST["tag"])) {
    $tag = $_POST["tag"];
} else {
    echo "no tag";
    exit;
}

if (isset($_POST["data"])) {
    $data = $_POST["data"];
} else {
    echo "no data";
    exit;
}

$db_file = $dir_palicannon . 'dighest.db3';
PDO_Connect("$db_file");

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO dighest_index ('id','title','summary','tag','user','time') VALUES (NULL,?,?,?,?,?)";
$stmt = $PDO->prepare($query);

$newData = array($title, $summary, $tag, 4, time());
$stmt->execute($newData);

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {

}
//获取刚刚插入的书摘记录的索引号
$new_index = $PDO->lastInsertId();

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO dighest ('id','index_id','album','book','paragraph') VALUES (NULL,?,?,?,?)";
$stmt = $PDO->prepare($query);
$dighest_par_array = str_getcsv($data);
$first_album = -1;
$first_book = -1;
$first_paragraph = -1;
foreach ($dighest_par_array as $value) {
    $one_recorder = str_getcsv($value, "-");
    if (count($one_recorder) >= 3) {
        if ($first_album == -1) {
            $first_album = $one_recorder[0];
            $first_book = $one_recorder[1];
            $first_paragraph = $one_recorder[2];
        }
        $newData = array($new_index, $one_recorder[0], $one_recorder[1], $one_recorder[2]);
        $stmt->execute($newData);
    }
}

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {

}

//更新索引数据库
$db_file = _FILE_DB_RESRES_INDEX_;
PDO_Connect("$db_file");

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO 'index' ('id','book','paragraph','level','type','language','title','author','editor','edition','share','album','update_time') VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);

$newData = array($first_book, $first_paragraph, $new_index, 'dighest', 'sc', $title, 4, 4, 1, 4, $first_album, time());
$stmt->execute($newData);

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {

}

//更新tag数据库
$db_file = _FILE_DB_RESRES_INDEX_;
PDO_Connect("$db_file");

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO 'tag' ('id','book','paragraph','title','summary','tag','language','author','time') VALUES (NULL,?,?,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);

$newData = array(1024, $new_index, $title, $summary, $tag, 'sc', 1, time());
$stmt->execute($newData);

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {

}
