<?php
#根据pali_text 里面的*_title.csv数据更新数据库
require_once "install_head.php";
require_once "../public/_pdo.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Update Pali Text Use CSV</h2>
<p><a href="index.php">Home</a></p>
<?php

if (isset($_GET["from"]) == false) {
    ?>
<form action="db_update_palitext.php" method="get">
From: <input type="text" name="from" value="0"><br>
To: <input type="text" name="to" value="216"><br>
<input type="submit">
</form>
<?php
return;
}

$from = $_GET["from"];
$to = $_GET["to"];
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
echo "doing:" . $xmlfile . "<br>";

$log = $log . date("Y-m-d h:i:sa") . ",$from,$FileName,open\r\n";

$arrInserString = array();

// 打开vri html文件并读取数据
$pali_text_array = array(); //vri text
if (($fpPaliText = fopen($dirPaliTextBase . $xmlfile, "r")) !== false) {
    while (($data = fgets($fpPaliText)) !== false) {
        array_push($pali_text_array, $data);
    }
    fclose($fpPaliText);
    echo "pali text load：" . $dirPaliTextBase . $xmlfile . "<br>";
} else {
    echo "can not pali text file. filename=" . $dirPaliTextBase . $xmlfile;
}

// 打开csv文件并读取数据
$inputRow = 0;
if (($fp = fopen(_DIR_PALI_TITLE_ . "/" . ($from + 1) . "_title.csv", "r")) !== false) {
    while (($data = fgetcsv($fp, 0, ',')) !== false) {
        if ($inputRow > 0) {
            $params = $data;
            array_push($arrInserString, $params);
        }
        $inputRow++;
    }
    fclose($fp);
    echo "单词表load：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv<br>";
} else {
    echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
}

if ((count($arrInserString)) != count($pali_text_array) - 2) {
    $log = $log . "$from, $FileName,error,文件行数不匹配 csv = " . (count($arrInserString) - 1) . " pali_text_array=" . (count($pali_text_array) - 2) . " \r\n";
}

$book = $from + 1;

//计算段落信息，如上一段
PDO_Connect(_FILE_DB_PALITEXT_,_DB_USERNAME_,_DB_PASSWORD_);
$query = "SELECT * from "._TABLE_PALI_TEXT_." where book = '$book'  order by paragraph asc";
$title_data = PDO_FetchAll($query);
echo "Paragraph Count:" . count($title_data) . " arrInserString:".count($arrInserString)." <br>";

$paragraph_count = count($title_data);

// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
$query = "UPDATE "._TABLE_PALI_TEXT_." SET level = ? , toc = ? , chapter_len = ? , next_chapter = ?, prev_chapter=? , parent= ?  ,  chapter_strlen = ?  WHERE book=? and paragraph=?";
$stmt = $PDO->prepare($query);

$paragraph_info = array();
array_push($paragraph_info, array($from, -1, $paragraph_count, -1, -1, -1));


for ($iPar = 0; $iPar < count($title_data); $iPar++) {
    $title_data[$iPar]["level"] = $arrInserString[$iPar][3];
}


for ($iPar = 0; $iPar < count($title_data); $iPar++) {
    $book = $from + 1;
    $paragraph = $title_data[$iPar]["paragraph"];

    if ((int) $title_data[$iPar]["level"] == 8) {
        $title_data[$iPar]["level"] = 100;
    }

    $curr_level = (int) $title_data[$iPar]["level"];
    # 计算这个chapter的段落数量
    $length = -1;
   
    
    for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
        $thislevel = (int) $title_data[$iPar1]["level"];
        if ($thislevel <= $curr_level) {
            $length = (int) $title_data[$iPar1]["paragraph"] - $paragraph;
            break;
        }
    }

    if ($length == -1) {
        $length = $paragraph_count - $paragraph + 1;
    }


    $prev = -1;
    if ($iPar > 0) {
        for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
            if ($title_data[$iPar1]["level"] == $curr_level) {
                $prev = $title_data[$iPar1]["paragraph"];
                break;
            }
        }
    }

    $next = -1;
    if ($iPar < count($title_data) - 1) {
        for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
            if ($title_data[$iPar1]["level"] == $curr_level) {
                $next = $title_data[$iPar1]["paragraph"];
                break;
            }
        }
    }

    $parent = -1;
    if ($iPar > 0) {
        for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
            if ($title_data[$iPar1]["level"] < $curr_level) {
                $parent = $title_data[$iPar1]["paragraph"];
                break;
            }
        }
    }
    //计算章节包含总字符数
    $iChapter_strlen = 0;

    for ($i = $iPar; $i < $iPar + $length; $i++) {
        $iChapter_strlen += $title_data[$i]["lenght"];
    }
    
    $newData = array(
        $arrInserString[$iPar][3],
        $arrInserString[$iPar][5],
        $length,
        $next,
        $prev,
        $parent,
        $iChapter_strlen,
        $book,
        $paragraph,
    );
    $stmt->execute($newData);

    if ($curr_level > 0 && $curr_level < 8) {
        array_push($paragraph_info, array($book, $paragraph, $length, $prev, $next, $parent));
    }
}

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";

    $log = $log . "$from, $FileName, error, $error[2] \r\n";
} else {
    $count = count($title_data);
    echo "updata $count paragraph info recorders.<br>";
    echo count($paragraph_info) . " Heading<br>";
}
//段落信息结束

$myLogFile = fopen(_DIR_LOG_ . "/db_update_palitext.log", "a");
fwrite($myLogFile, $log);
fclose($myLogFile);

?>


<?php
if ($from == $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_update_palitext.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
