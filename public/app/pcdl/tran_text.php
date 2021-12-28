<?php
include "./config.php";
include "./_pdo.php";

if (isset($_POST["album"])) {
    $album = $_POST["album"];
} else {
    echo "no album";
    exit;
}
if (isset($_POST["data"])) {
    $data = $_POST["data"];
} else {
    echo "no data";
    exit;
}

$log = "";

PDO_Connect(_FILE_DB_RESRES_INDEX_);
$query = "select * from 'album' where id='$album'";
$Fetch = PDO_FetchAll($query);
$iFetch = count($Fetch);
if ($iFetch > 0) {
    if ($Fetch[0]["type"] == "translate") {
        $tocHtml = "";
        //打开翻译数据文件
        $db_file = $dir_appdata . $Fetch[0]["file"];

        PDO_Connect("$db_file");

        // 开始一个事务，关闭自动提交
        $PDO->beginTransaction();
        $query = "UPDATE 'data' SET 'text' = ? WHERE paragraph = ?";
        $stmt = $PDO->prepare($query);
        $dighest_par_array = str_getcsv($data, "#");
        foreach ($dighest_par_array as $value) {
            $one_recorder = str_getcsv($value, "@");
            if (count($one_recorder) >= 4) {
                $newData = array($one_recorder[3], $one_recorder[2]);
                $log .= "par:" . $one_recorder[2];
                $stmt->execute($newData);
            }
        }

        // 提交更改
        $PDO->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            echo "error - $error[2] <br>";
        } else {
            echo count($dighest_par_array) . "个段落被修改";
        }

    }
}
