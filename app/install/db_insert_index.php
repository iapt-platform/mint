<?php
/*
用csv 单词列表文件更新 wordindex and word
可以用 db_insert_word_from_csv.php取代
*/
require_once "install_head.php";

function dict_lookup($word)
{
    global $dbh_word_index;
    $query = "select * from wordindex where \"word\" = ? ";
    $stmt = $dbh_word_index->prepare($query);
    $stmt->execute(array($word));
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function getWordEn($strIn)
{
    $out = $strIn;
    $out = str_replace("ā", "a", $out);
    $out = str_replace("ī", "i", $out);
    $out = str_replace("ū", "u", $out);
    $out = str_replace("ṅ", "n", $out);
    $out = str_replace("ñ", "n", $out);
    $out = str_replace("ṭ", "t", $out);
    $out = str_replace("ḍ", "d", $out);
    $out = str_replace("ṇ", "n", $out);
    $out = str_replace("ḷ", "l", $out);
    $out = str_replace("ṃ", "m", $out);
    return ($out);
}
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Insert to Index</h2>
<p><a href="index.php">Home</a></p>
<?php
include "./_pdo.php";
include "../config.php";
if (isset($_GET["from"]) == false) {
    ?>
<form action="db_insert_index.php" method="get">
From: <input type="text" name="from" value="0"><br>
To: <input type="text" name="to" value="216"><br>
<input type="submit">
</form>
<?php
return;
}

$from = $_GET["from"];
$to = $_GET["to"];

$dirLog = _DIR_LOG_ . "/";
$dirDb = "db/";
$dirXmlBase = _DIR_PALI_CSV_ . "/";

$filelist = array();
$fileNums = 0;
$log = "";
echo "<h2>$from</h2>";

//已经存在的词
$g_wordCounter = 0;
$g_wordIndexCounter = 0;
$iAllWordIndex = array();
$sAllWord = array();
//新加入的词
$wordindex_max_index = 0;
$aNewWordIndex = array(); //词内容
$sNewWord = array(); //词头索引

global $dbh_word_index;
$dns = _FILE_DB_WORD_INDEX_;
$dbh_word_index = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_word_index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$query = "SELECT id from "._TABLE_WORD_INDEX_." where true order by id DESC ";
$stmt = $dbh_word_index->prepare($query);
$stmt->execute(array());
$id = $stmt->fetch(PDO::FETCH_ASSOC);
if ($id === false) {
    $wordindex_max_index = 0;
} else {
    $wordindex_max_index = $id["id"];
}
$db_file = _FILE_DB_PALI_INDEX_;
PDO_Connect($db_file,_DB_USERNAME_,_DB_PASSWORD_);
$query = "SELECT id from "._TABLE_WORD_." where true order by id DESC ";
$stmt = $PDO->prepare($query);
$stmt->execute(array());
$id = $stmt->fetch(PDO::FETCH_ASSOC);
if ($id === false) {
    $g_wordCounter = 0;
} else {
    $g_wordCounter = $id["id"];
}



if (($handle = fopen("filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to >= $fileNums) {
    $to = $fileNums - 1;
}

$iFile = $from;
{

    $FileName = $filelist[$iFile][1] . ".htm";
    $fileId = $filelist[$iFile][0];

    $inputFileName = $FileName;
    $outputFileNameHead = $filelist[$iFile][1];
    $bookId = $filelist[$iFile][2];

    $dirXml = $outputFileNameHead . "/";

    $xmlfile = $inputFileName;
    echo "doing:" . $xmlfile . "<br>";
    $log = $log . "$iFile,$FileName,open\r\n";

    $arrInserString = array();

    // 打开文件并读取数据
    $irow = 0;
    if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            $irow++;
            if ($irow > 1) {
                $params = $data;
                $arrInserString[] = $params;
            }
        }
        fclose($fp);
        echo "单词表load：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv<br>";
    } else {
        echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
    }

    // 开始一个事务，关闭自动提交
    $PDO->beginTransaction();
    $query = "INSERT INTO "._TABLE_WORD_." ( id , book , paragraph , wordindex , bold ) VALUES (?,?,?,?,?)";
    $stmt = $PDO->prepare($query);
    $count = 0;
    $count1 = 0;
    $sen = "";
    $sen1 = "";
    $sen_en = "";
    $sen_count = 0;
    $book = "";
    $paragraph = "";
    foreach ($arrInserString as $oneParam) {
        if ($oneParam[5] != "") {
            $g_wordCounter++;
            $book = substr($oneParam[2], 1);
            $paragraph = $oneParam[3];
            $word = $oneParam[5];
            if ($oneParam[15] == "bld") {
                $bold = 1;
            } else {
                $bold = 0;
            }

            if (isset($sAllWord[$word])) {
                //已经存在的词
                $wordindex = $sAllWord[$word];

                $iAllWordIndex[$wordindex][1]++;
                if ($bold == 1) {
                    $iAllWordIndex[$wordindex][3]++;
                } else {
                    $iAllWordIndex[$wordindex][2]++;
                }

            } else if (isset($sNewWord[$word])) {
                //是新家入的词
                $wordindex = $sNewWord[$word];

                $aNewWordIndex[$wordindex][1]++;
                if ($bold == 1) {
                    $aNewWordIndex[$wordindex][3]++;
                } else {
                    $aNewWordIndex[$wordindex][2]++;
                }
            } else if (($lookup = dict_lookup($word)) !== false) {
                //在数据库中找到
                $wordindex = $lookup["id"];
                $sAllWord[$word] = $wordindex;
                $iAllWordIndex[$wordindex][0] = $word;

                $iAllWordIndex[$wordindex][1] = $lookup["count"] + 1; //all word count
                if ($bold == 1) {
                    $iAllWordIndex[$wordindex][2] = $lookup["normal"];
                    $iAllWordIndex[$wordindex][3] = $lookup["bold"] + 1;
                } else {
                    $iAllWordIndex[$wordindex][2] = $lookup["normal"] + 1;
                    $iAllWordIndex[$wordindex][3] = $lookup["bold"];
                }
            } else {
                //数据库里也没找到 怎么办呢？我想呀想 想呀想
                $wordindex = $wordindex_max_index + 1;
                $sNewWord[$word] = $wordindex;

                $aNewWordIndex[$wordindex][0] = $word;

                $aNewWordIndex[$wordindex][1] = 1; //all word count
                if ($bold == 1) {
                    $aNewWordIndex[$wordindex][2] = 0;
                    $aNewWordIndex[$wordindex][3] = 1;
                } else {
                    $aNewWordIndex[$wordindex][2] = 1;
                    $aNewWordIndex[$wordindex][3] = 0;
                }

                $wordindex_max_index++;
            }

            $newWord = array($g_wordCounter, $book, $paragraph, $wordindex, $bold);
            $stmt->execute($newWord);
            $count++;
        }

    }

    // 提交更改
    $PDO->commit();
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        echo "error - $error[2] <br>";
        $log .= "$from, $FileName, error, $error[2] \r\n";
    } else {
        echo "updata $count recorders.<br />";
        $log .= "updata $count recorders.\r\n";
    }

}
//更新单词索引表

//首先插入新的词
// 开始一个事务，关闭自动提交
$dbh_word_index->beginTransaction();
$query = "INSERT INTO "._TABLE_WORD_INDEX_." ('id','word','word_en','count','normal','bold','is_base','len') VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )";
$stmt = $dbh_word_index->prepare($query);

echo "INSERT:" . count($aNewWordIndex) . "words<br>";
foreach ($aNewWordIndex as $wIndex => $info) {
    $wordindex = $iword;
    $newWord = array(
        $wIndex,
        $info[0],
        getWordEn($info[0]),
        $info[1],
        $info[2],
        $info[3],
        0,
        mb_strlen($info[0], "UTF-8"),
    );
    $stmt->execute($newWord);
}
// 提交更改
$dbh_word_index->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = $dbh_word_index->errorInfo();
    echo "error - $error[2] <br>";
    $log .= "$from, $FileName, error, $error[2] \r\n";
} else {
    echo "updata iword recorders.<br />";
    $log .= "updata iword recorders.\r\n";
}

//然后修改已经有的词
// 开始一个事务，关闭自动提交
$dbh_word_index->beginTransaction();
$query = "UPDATE "._TABLE_WORD_INDEX_." SET count = ? , normal = ? , bold = ?   where  id = ?  ";
$stmt = $dbh_word_index->prepare($query);

echo "UPDATE:" . count($iAllWordIndex) . "words<br>";
foreach ($iAllWordIndex as $wIndex => $info) {
    $wordindex = $iword;
    $newWord = array(
        $info[1],
        $info[2],
        $info[3],
        $wIndex,
    );
    $stmt->execute($newWord);
}
// 提交更改
$dbh_word_index->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = $dbh_word_index->errorInfo();
    echo "error - $error[2] <br>";
    $log .= "$from, $FileName, error, $error[2] \r\n";
} else {
    echo "updata iword recorders.<br />";
    $log .= "updata iword recorders.\r\n";
}

$myLogFile = fopen($dirLog . "insert_index.log", "a");
fwrite($myLogFile, $log);
fclose($myLogFile);

?>


<?php

if ($from >= $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_insert_index.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
