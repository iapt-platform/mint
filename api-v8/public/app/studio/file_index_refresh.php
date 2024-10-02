<?php
include "../config.php";
include "./_pdo.php";

//获取服务器端文件列表
$dir = _DIR_USER_DOC_ . '/' . $_COOKIE["userid"] . '/' . _DIR_MYDOCUMENT_ . "/";

PDO_Connect( _FILE_DB_FILEINDEX_);

$files = scandir($dir);
$arrlength = count($files);

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO fileindex ('id','file_name','title','create_time','modify_time','accese_time','file_size') VALUES (NULL,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);

for ($x = 0; $x < $arrlength; $x++) {
    if (is_file($dir . $files[$x])) {
        $ctime = filectime($dir . $files[$x]);
        $mtime = filemtime($dir . $files[$x]);
        $atime = fileatime($dir . $files[$x]);
        $filesize = filesize($dir . $files[$x]);
        $newData = array($files[$x], "title", $ctime, $mtime, $atime, $filesize);
        $stmt->execute($newData);
        //echo $files[$x].',';
    }
}

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
} else {
    echo "updata $arrlength recorders.";
}
