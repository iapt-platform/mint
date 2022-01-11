<?php
require_once "install_head.php";

require_once '../config.php';
require_once "../public/_pdo.php";
require_once "../public/function.php";

function getWordEn($strIn)
{
    $strIn = strtolower($strIn);
    $search = array('ā', 'ī', 'ū', 'ṅ', 'ñ', 'ṭ', 'ḍ', 'ṇ', 'ḷ', 'ṃ');
    $replace = array('a', 'i', 'u', 'n', 'n', 't', 'd', 'n', 'l', 'm');
    return (str_replace($search, $replace, $strIn));
}
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Update Table of Contence</h2>
<?php

if (isset($_GET["from"]) == false) {
    ?>
<form action="db_update_toc.php" method="get">
From: <input type="text" name="from" value="0"><br>
To: <input type="text" name="to" value="216"><br>
File: <input type="text" name="file" value="title"><br>
Author: <input type="text" name="author" value=""><br>
Language:
<select name="lang">
<option value="pali">pali</option>
<option value="en">English</option>
<option value="zh-hans">简体中文</option>
<option value="zh-hant">繁体中文</option>
</select>
<br>
<input type="submit">
</form>
<?php
return;
}

$from = $_GET["from"];
$to = $_GET["to"];
$_file = $_GET["file"];
$_author = $_GET["author"];
$_lang = $_GET["lang"];
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

$dirDb = "/";
$inputFileName = $FileName;
$outputFileNameHead = $filelist[$from][1];
$bookId = $filelist[$from][2];
$vriParNum = 0;
$wordOrder = 1;

$dirXmlBase = _DIR_PALI_CSV_ . "/";
$dirPaliTextBase = _DIR_PALI_HTML_ . "/";
$dirXml = $outputFileNameHead . "/";

$xmlfile = $inputFileName;

$log = $log . date("Y-m-d h:i:sa") . ",$from,$FileName,open\r\n";

$arrInserString = array();

// 打开csv文件并读取数据
$inputRow = 0;
if (($fp = fopen(_DIR_PALI_TITLE_ . "/" . ($from + 1) . "_{$_file}.csv", "r")) !== false) {
    while (($data = fgetcsv($fp, 0, ',')) !== false) {
        if ($inputRow > 0 && $data[3] != 100 && !empty($data[6])) {
            array_push($arrInserString, $data);
        }
        $inputRow++;
    }
    fclose($fp);
    echo "res load：" . _DIR_PALI_TITLE_ . "/" . ($from + 1) . "_title.csv<br>";
} else {
    echo "can not open csv ";

    if ($from == $to) {
        echo "<h2>齐活！功德无量！all done!</h2>";
    } else {
        echo "<script>";
        $next = $from + 1;
        echo "window.location.assign(\"db_update_toc.php?from={$next}&to={$to}&file={$_file}&author={$_author}&lang={$_lang}\")";
        echo "</script>";
        echo "正在载入:" . ($from + 1);
        echo "</body></html>";
        exit;
    }
}

$book = $from + 1;

//删除已有标题
PDO_Connect(_FILE_DB_RESRES_INDEX_);
$query = "DELETE FROM \""._TABLE_RES_INDEX_."\" WHERE book = ?  AND  language = ?  ";
PDO_Execute($query, array($book,$_lang));


// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO \""._TABLE_RES_INDEX_."\" (book , paragraph, title, title_en , level, type , language , author , share , create_time , update_time  ) VALUES (  ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )";
$stmt = $PDO->prepare($query);
if ($_lang == "pali") {
    $type = 1;
} else {
    $type = 2;
}
foreach ($arrInserString as $title) {
    if (isset($title[7])) {
        $author = $title[7];
    } else if (isset($_author)) {
        $author = $_author;
    } else {
        $author = "";
    }
	$title[6] = mb_substr($title[6],0,1024);
    $newData = array(
        $book,
        $title[2],
        $title[6],
        getWordEn($title[6]),
        $title[3],
        $type,
        $_lang,
        $author,
        1,
        mTime(),
        mTime(),
    );
    $stmt->execute($newData);
}
// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";
    $log = $log . "$from, error, $error[2] \r\n";
} else {
    $count = count($arrInserString);
    echo "updata $count title recorders.<br>";
}
//段落信息结束
$myLogFile = fopen(_DIR_LOG_ . "/db_update_toc.log", "a");
fwrite($myLogFile, $log);
fclose($myLogFile);

?>


<?php
if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    $next = $from + 1;
    echo "window.location.assign(\"db_update_toc.php?from={$next}&to={$to}&file={$_file}&author={$_author}&lang={$_lang}\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
