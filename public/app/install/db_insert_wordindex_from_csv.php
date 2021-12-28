<?php
require_once "install_head.php";
include "./_pdo.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Insert to Index</h2>
<?php

if (isset($_GET["from"]) == false) {
    $from = 0;
} else {
    $from = $_GET["from"];
}

$dirLog = _DIR_LOG_ . "/";

$filelist = array();
$fileNums = 0;
$log = "";
echo "<h2>doing : No.{$from}  </h2>";

global $dbh_word_index;
$dns = _FILE_DB_WORD_INDEX_;
$dbh_word_index = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_word_index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if (($fpoutput = fopen(_DIR_CSV_PALI_CANON_WORD_INDEX_ . "/{$from}.csv", "r")) !== false) {

    // 开始一个事务，关闭自动提交
    $dbh_word_index->beginTransaction();
    $query = "INSERT INTO "._TABLE_WORD_INDEX_." (id , word , word_en , count , normal , bold , is_base , len ) VALUES (?,?,?,?,?,?,?,?)";

    $stmt = $dbh_word_index->prepare($query);

    $count = 0;
    while (($data = fgetcsv($fpoutput, 0, ',')) !== false) {
        $stmt->execute($data);
        $count++;
    }
    // 提交更改
    $dbh_word_index->commit();
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = $dbh_word_index->errorInfo();
        echo "error - $error[2] <br>";
        $log .= "$from, $FileName, error, $error[2] \r\n";
    } else {
        echo "updata $count recorders.<br />";
        $log .= "updata $count recorders.\r\n";
    }
} else {
    echo "<h2>齐活！功德无量！all done!</h2>";
    exit;
}

echo "<script>";
echo "window.location.assign(\"db_insert_wordindex_from_csv.php?from=" . ($from + 1) . "\")";
echo "</script>";
echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];

?>
</body>
</html>
