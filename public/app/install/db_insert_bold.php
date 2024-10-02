<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Insert to bold</h2>
<p><a href="index.php">Home</a></p>
<div>
生成黑体字数据库。黑体字多数是义注复注里的单词（尤其是专有名词）解释。
</div>
<?php
require_once "./_pdo.php";
require_once "../config.php";

if (isset($_GET["from"]) == false) {
    ?>
<form action="db_insert_bold.php" method="get">
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
function getWordEn($strIn)
{
    $search = array('ā', 'ī', 'ū', 'ṅ', 'ñ', 'ṭ', 'ḍ', 'ṇ', 'ḷ', 'ṃ');
    $replace = array('a', 'i', 'u', 'n', 'n', 't', 'd', 'n', 'l', 'm');
    return (str_replace($search, $replace, $strIn));
}

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

//输出文件夹
$dirDb = _DIR_PALICANON_ . "/";
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



// 打开文件并读取数据
if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
    while (($data = fgetcsv($fp, 0, ',')) !== false) {
        $params = $data;
        $arrInserString[count($arrInserString)] = $params;
    }
    fclose($fp);
    echo "单词表load：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv<br>";

	PDO_Connect(_FILE_DB_BOLD_);

	$query = "DELETE FROM "._TABLE_WORD_BOLD_." WHERE book=?";
	PDO_Execute($query,array($from+1));
	// 开始一个事务，关闭自动提交
	$PDO->beginTransaction();
	$query = "INSERT INTO "._TABLE_WORD_BOLD_." (book , paragraph , word , word2 , word_en ) VALUES (?,?,?,?,?)";
	$stmt = $PDO->prepare($query);
	$allcount = 1;
	$count = 0;
	$count1 = 0;
	$sen = "";
	$sen1 = "";
	$sen_en = "";
	$sen_count = 0;
	$book = "";
	$paragraph = "";
	foreach ($arrInserString as $oneParam) {
		if ($oneParam[15] == "bld") {
			if ($oneParam[5] != "") {
				$sen_count++;
			}
			$sen .= $oneParam[4] . " ";
			$sen1 .= $oneParam[5] . " ";
			$book = substr($oneParam[2], 1);
			$paragraph = $oneParam[3];
			if ($oneParam[5] != "") {
				$newWord = array($book, $paragraph, $oneParam[4], $oneParam[5], getWordEn($oneParam[5]));
				$stmt->execute($newWord);
				$count++;
				$allcount++;
			}
		} else {
			if ($sen_count > 1) {
				$sen = rtrim($sen);
				$sen1 = rtrim($sen1);
				$sen_en = getWordEn($sen1);
				$newWord = array($book, $paragraph, $sen, $sen1, $sen_en);
				$stmt->execute($newWord);
				$count1++;
				$allcount++;
				$sen = "";
				$sen1 = "";
				$sen_en = "";
				$sen_count = 0;
			} else {
				$sen = "";
				$sen1 = "";
				$sen_en = "";
				$sen_count = 0;
			}
		}
	}
	// 提交更改
	$PDO->commit();
	if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
		$error = PDO_ErrorInfo();
		echo "error - $error[2] <br>";

		$log = $log . "$from, $FileName, error, $error[2] \r\n";
	} else {
		echo "updata $count-$count1 recorders.";
	}

	$myLogFile = fopen($dirLog . "insert_bold.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);
	
} else {
    echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
}


?>


<?php
if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_insert_bold.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
