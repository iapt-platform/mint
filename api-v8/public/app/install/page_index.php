<?php
/*
用拆分好的三藏数据 导出cs6段落编号
 */

?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Export Paragraph No.</h2>
<p>用拆分好的三藏数据 导出cs6段落编号</p>
<?php
require_once '../public/_pdo.php';
require_once '../config.php';
if (isset($_GET["run"]) == false) {
    ?>
<form action="db_insert_templet.php" method="get">
From: <input type="text" value="0" name="from"><br>
To: <input type="text" value="216" name="to"><br>
<input type="submit">
</form>
<?php
return;
}

$from = 0;
$to = 216;
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

$dirLog = _DIR_LOG_;

$dirDb = _DIR_PALICANON_TEMPLET_;
$inputFileName = $FileName;
$outputFileNameHead = $filelist[$from][1];
$bookId = $filelist[$from][2];
$vriParNum = 0;
$wordOrder = 1;

$dirXmlBase = _DIR_PALI_CSV_ . "/";
$dirXml = $outputFileNameHead . "/";

$currChapter = "";
$currParNum = "";
$arrAllWords[0] = array("id", "wid", "book", "paragraph", "word", "real", "type", "gramma", "mean", "note", "part", "partmean", "bmc", "bmt", "un", "style", "vri", "sya", "si", "ka", "pi", "pa", "kam");
$g_wordCounter = 0;

$arrUnWords[0] = array("id", "word", "type", "gramma", "parent", "mean", "note", "part", "partmean", "cf", "state", "delete", "tag", "len");
$g_unWordCounter = 0;

$arrUnPart[0] = "word";
$g_unPartCounter = -1;

/*去掉标点符号的统计*/
$arrAllPaliWordsCount = array();
$g_paliWordCounter = 0;
$g_wordCounterInSutta = 0;
$g_paliWordCountCounter = 0;

$xmlfile = $inputFileName;
echo "doing:" . $xmlfile . "<br>";
$log = $log . "$from,$FileName,open\r\n";

$arrInserString = array();
$db_file = $dirDb . "/" . $bookId . '_tpl.db3';
PDO_Connect("sqlite:{$db_file}");

PDO_Execute("DROP TABLE IF EXISTS main;");
$query = "CREATE TABLE 'main' ( 'id' TEXT PRIMARY KEY NOT NULL,
							'book' INTEGER,
							'paragraph' INTEGER,
							'wid' INTEGER,
							'word' TEXT,
							'real' TEXT,
							'type' TEXT,
							'gramma' TEXT,
							'part' TEXT,
							'style' TEXT)";
$stmt = @PDO_Execute($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    print_r($error[2]);

}
PDO_Execute("DROP INDEX IF EXISTS search;");

$query = "CREATE INDEX 'search' ON \"main\" (\"book\", \"paragraph\", \"wid\" ASC)";
$stmt = @PDO_Execute($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    print_r($error[2]);
    $log = $log . "$from, $FileName, error, $error[2] \r\n";
}

// 打开文件并读取数据
if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
    while (($data = fgetcsv($fp, 0, ',')) !== false) {
        //id,wid,book,paragraph,word,real,type,gramma,mean,note,part,partmean,bmc,bmt,un,style,vri,sya,si,ka,pi,pa,kam

        $params = array($data[0],
            mb_substr($data[2], 1),
            $data[3],
            $data[16],
            $data[4],
            $data[5],
            $data[6],
            $data[7],
            $data[10],
            $data[15]);
        $arrInserString[] = $params;
    }
    fclose($fp);
    echo "单词表load：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv<br>";
} else {
    echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
}

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "INSERT INTO main ('id','book','paragraph','wid','word','real','type','gramma','part','style') VALUES (?,?,?,?,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);
foreach ($arrInserString as $oneParam) {
    $stmt->execute($oneParam);
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

$myLogFile = fopen($dirLog . "insert_db.log", "a");
fwrite($myLogFile, $log);
fclose($myLogFile);
?>


<?php
if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_insert_templet.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
