<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Insert Pali Text To DB</h2>
<p><a href="index.php">Home</a></p>
<?php
require_once "./_pdo.php";
require_once "../config.php";
if (isset($_GET["from"]) == false) {
    ?>
<form action="db_insert_palitext.php" method="get">
From: <input type="text" name="from" value="0"><br>
To: <input type="text" name="to" value="216"><br>
<input type="submit">
</form>
<?php
return;
}

$from = (int)$_GET["from"];
$to = (int)$_GET["to"];
$filelist = array();
$fileNums = 0;
$log = "";
echo "<h2>$from</h2>";

if (($handle = fopen("filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to >= $fileNums) {
    $to = $fileNums - 1;
}

$FileName = $filelist[$from][1] . ".htm";
$fileId = $filelist[$from][0];
$fileId = $filelist[$from][0];

$dirLog = _DIR_LOG_ . "/";

$inputFileName = $FileName;
$outputFileNameHead = $filelist[$from][1];
$bookId = $filelist[$from][2];
$vriParNum = 0;
$wordOrder = 1;

$dirXmlBase = _DIR_PALI_CSV_ . "/";
$dirPaliTextBase = _DIR_PALI_HTML_ . "/";
$dirXml = $outputFileNameHead . "/";

$xmlfile = $inputFileName;
echo "doing:" . $xmlfile . "<br>";
$log = $log . "$from,$FileName,open\r\n";

$arrInserString = array();
PDO_Connect(_FILE_DB_PALITEXT_,_DB_USERNAME_,_DB_PASSWORD_);

// 打开vri html文件并读取数据
$pali_text_array = array();
if (($fpPaliText = fopen($dirPaliTextBase . $xmlfile, "r")) !== false) {
    while (($data = fgets($fpPaliText)) !== false) {
        if (substr($data, 0, 2) === "<p") {
            array_push($pali_text_array, $data);
        }

    }
    fclose($fpPaliText);
    echo "pali text load：" . $dirPaliTextBase . $xmlfile . "<br>";
} else {
    echo "can not pali text file. filename=" . $dirPaliTextBase . $xmlfile;
}

$inputRow = 0;
if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . "_pali.csv", "r")) !== false) {
    while (($data = fgetcsv($fp, 0, ',')) !== false) {
        if ($inputRow > 0) {
            if (($inputRow - 1) < count($pali_text_array)) {
                $data[5] = $pali_text_array[$inputRow - 1];
            }
            $arrInserString[] = $data;
        }
        $inputRow++;
    }
    fclose($fp);
    echo "单词表load：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv<br>";
} else {
    echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
}

if (($inputRow - 1) != count($pali_text_array)) {
    $log = $log . "$from, $FileName,error,文件行数不匹配 inputRow=$inputRow pali_text_array=" . count($pali_text_array) . " \r\n";
}

$query = "DELETE FROM "._TABLE_PALI_TEXT_." WHERE book=?";
PDO_Execute($query,array($from+1));

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();

$query = "INSERT INTO "._TABLE_PALI_TEXT_." ( book , paragraph , level , class , toc , text , html , lenght ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )";
$stmt = $PDO->prepare($query);
foreach ($arrInserString as $oneParam) {
    if ($oneParam[3] < 100) {
        $toc = $oneParam[6];
    } else {
        $toc = "";
    }
    $newData = array($from + 1, $oneParam[2], $oneParam[3], $oneParam[4], $toc, $oneParam[6], $oneParam[5], mb_strlen($oneParam[6], "UTF-8"));
    $stmt->execute($newData);
}
// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";

    $log = $log . "$from, $FileName, error, $error[2] \r\n";
} else {
    $count = count($arrInserString);
    echo "updata $count recorders.";
}

$myLogFile = fopen($dirLog . "db_insert_palitext.log", "a");
fwrite($myLogFile, $log);
fclose($myLogFile);
?>


<?php
if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_insert_palitext.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
