<!--句子库生成-->
<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Insert to Sentence DB</h2>
<p><a href="index.php">Home</a></p>
<?php
include "./_pdo.php";
require_once '../config.php';

$db_file = _FILE_DB_PALI_SENTENCE_;
$thisfile = '.' . mb_substr(__FILE__, mb_strlen(__DIR__));
if (isset($_GET["from"]) == false) {
    ?>
<form action="<?php echo $thisfile; ?>" method="get">
From: <input type="text" value="0" name="from"><br>
To: <input type="text" value="216" name="to"><br>
<input type="submit">
</form>
<?php

    return;
}
function wordStyle($word, $style)
{
    switch ($style) {
        case 'bld':
            # bold form
            # 不包含字符{ }
            if (mb_strpos($word, '{', 0, "UTF-8") === false) {
                return "<b>" . $word . "</b> ";
            } else {
                $word = str_replace("{", "<b>", $word);
                $word = str_replace("}", "</b>", $word);
                return $word;
            }
            break;

        case 'note':
            # vir note...
            return "<n>" . $word . "</n>";
            break;
        case 'paranum':
            # vir note...
            return "<paranum>" . $word . "</paranum>";
            break;

        default:
            # code...
            return $word;
            break;
    }
}

$from = $_GET["from"];
$to = $_GET["to"];
$filelist = array();
$fileNums = 0;
$log = "";
echo "<h2>$from-$to</h2>";

if (($handle = fopen("filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to >= $fileNums) {
    $to = $fileNums - 1;
}

$FileName = $filelist[$from][1] . ".htm";
$fileId = $filelist[$from][0];
$fileId = $filelist[$from][0];

$dirLog = _DIR_LOG_ . "/";

$dirDb = "db/";
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

function getWordEn($strIn)
{
    $search = array('ā', 'ī', 'ū', 'ṅ', 'ñ', 'ṭ', 'ḍ', 'ṇ', 'ḷ', 'ṃ');
    $replace = array('a', 'i', 'u', 'n', 'n', 't', 'd', 'n', 'l', 'm');
    return (str_replace($search, $replace, $strIn));
}

// 打开文件并读取数据
$iWord = 0;
$pre = null;
$curr = null;
$next = null;
$wordlist = array();
$arrSent = array();
$book = 0;
$sent_html = "";

if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
    while (($data = fgetcsv($fp)) !== false) {
        //id,wid,book,paragraph,word,real,type,gramma,mean,note,part,partmean,bmc,bmt,un,style,vri,sya,si,ka,pi,pa,kam
        //$data = mb_split(",",$data);

        $wordlist[] = $data;
        if ($book == 0) {
            $book = substr($data[2], 1);
        }

    }
    fclose($fp);

    $iWord = 0;
    $iCurrPara = 0;
    $Note_Mark = 0;
    if ($wordlist[1][6] != ".ctl.") {
        $sent = $wordlist[1][4] . " ";
        $sent_html = wordStyle($wordlist[1][4], $wordlist[1][15]) . " ";
        $sent_real = $wordlist[1][5];
        $wordcount = 1;
    } else {
        $sent = "";
        $sent_html = "";
        $sent_real = "";
        $wordcount = 0;
    }
    $begin = 1;
    $end = 1;
    $iSent = 0;
    $Note_Mark1 = 0;
    $Note_Mark2 = 0;
    $Note_Mark = 0;
    $wordcount = 0;
    for ($i = 1; $i < count($wordlist); $i++) {
        if ($wordlist[$i][3] > $iCurrPara) {
            //echo  "new paragraph<br>";
            $iWord = 0;
            if ($i > 1) {
                //echo "上一段结束<br>";
                if (strlen(trim($sent)) > 0) {
                    $end = $wordlist[$i - 1][16];
                    $arrSent[] = array($book, $iCurrPara, $begin, $end, mb_strlen(trim($sent_real), "UTF-8"), $wordcount, $sent, $sent_html, trim($sent_real), getWordEn($sent_real));
                    //echo "end={$end}<br>";
                    //echo "<div>[{$iCurrPara}-{$begin}-{$end}]({$wordcount})<br>{$sent}<br>{$sent_real}<br>".getWordEn($sent_real)."</div>";
                }
                $iCurrPara = $wordlist[$i][3];

                $Note_Mark1 = 0;
                $Note_Mark2 = 0;
                $Note_Mark = 0;

                $pre = $wordlist[$i - 1];
                $curr = $wordlist[$i];
                if ($i < count($wordlist) - 1) {
                    $next = $wordlist[$i + 1];
                } else {
                    $next = "";
                }

                if ($next[4] == "(" || $curr[4] == "(") {
                    $Note_Mark1 = 1;
                } else if ($pre[4] == ")" && $Note_Mark1 == 1) {
                    $Note_Mark1 = 0;
                }

                if ($next[4] == "[" || $curr[4] == "[") {
                    $Note_Mark2 = 1;
                } else if ($pre[4] == "]" && $Note_Mark2 == 1) {
                    $Note_Mark2 = 0;
                }
                $Note_Mark = $Note_Mark1 + $Note_Mark2;
                //下一段开始
                if ($wordlist[$i][6] != ".ctl.") {
                    $sent = $wordlist[$i][4] . " ";
                    $sent_html = wordStyle($wordlist[$i][4], $wordlist[$i][15]) . " ";
                    if ($wordlist[$i][5] == '"') {
                        $sent_real = "";
                    } else {
                        $sent_real = $wordlist[$i][5];
                    }
                    $wordcount = 1;
                } else {
                    $sent = "";
                    $sent_html = "";
                    $sent_real = "";
                    $wordcount = 0;
                }
                $begin = $wordlist[$i][16];

                $iSent++;

                continue;
            }
            $iCurrPara = $wordlist[$i][3];
        }
        $isEndOfSen = false;
        if ($i < count($wordlist) - 1) {
            $pre = $wordlist[$i - 1];
            $curr = $wordlist[$i];
            if ($i < count($wordlist) - 1) {
                $next = $wordlist[$i + 1];
            } else {
                $next = "";
            }

            if ($next[4] == "(") {
                $Note_Mark1 = 1;
            } else if ($pre[4] == ")" && $Note_Mark1 == 1) {
                $Note_Mark1 = 0;
            }

            if ($next[4] == "[") {
                $Note_Mark2 = 1;
            } else if ($pre[4] == "]" && $Note_Mark2 == 1) {
                $Note_Mark2 = 0;
            }
            $Note_Mark = $Note_Mark1 + $Note_Mark2;

            if ($curr[15] != "note" || mb_substr($curr[1], 0, 5, "UTF-8") != "gatha") {
                if ($curr[4] == "." && !is_numeric($pre[4]) && $next[3] == $iCurrPara && $Note_Mark === 0) {
                    //以.結尾且非註釋
                    if ($next[4] != "(") {
                        $isEndOfSen = true;
                    }
                } else if ($curr[4] == "–" && $next[4] == "‘" && $Note_Mark === 0) {
                    $isEndOfSen = true;
                } else if ($Note_Mark == 0) {
                    //以!或?或;結尾
                    if ($curr[4] == "!") {
                        if ($next[4] != "!") {
                            if ($next[4] != "(") {
                                $isEndOfSen = true;
                            }
                        }
                    } else if ($curr[4] == ";" || $curr[4] == "?") {
                        if ($next[4] != "(") {
                            $isEndOfSen = true;
                        }
                    }
                }
            }
        }

        if ($curr[6] != ".ctl.") {
            if ($next[5] != "") {
                # 下一个是标点符号
                $sent .= $curr[4] . " ";
                $sent_html .= wordStyle($curr[4], $curr[15]) . " ";
            } else {
                $sent .= $curr[4];
                $sent_html .= wordStyle($curr[4], $curr[15]);
            }
            if ($wordlist[$i][5] != '' && ($Note_Mark == 0 || ($Note_Mark == 1 && ($next[4] == "[" || $next[4] == "(")))) {

                $wordcount++;
                if ($wordlist[$i][5] == "iti") {
                    $sent_real .= " " . $curr[4];
                } else {
                    $sent_real .= " " . $curr[5];
                }
            }

        }
        if ($isEndOfSen == true && strlen(trim($sent)) > 0) {
            $end = $wordlist[$i][16];
            $arrSent[] = array($book, $iCurrPara, $begin, $end, mb_strlen(trim($sent_real), "UTF-8"), $wordcount, $sent, $sent_html, trim($sent_real), getWordEn($sent_real));
            //echo "end={$end}<br>";
            //echo "<div>[{$iCurrPara}-{$begin}-{$end}]({$wordcount})<br>{$sent}<br>{$sent_real}<br>".getWordEn($sent_real)."</div>";

            $sent = "";
            $sent_html = "";
            $sent_real = "";
            $iSent++;
            $begin = $wordlist[$i][16] + 1;
            $wordcount = 0;
        }

        $iWord++;
    }
    if (strlen(trim($sent)) > 0) {
        $end = $wordlist[count($wordlist) - 1][16];
        $arrSent[] = array($book, $iCurrPara, $begin, $end, mb_strlen(trim($sent_real), "UTF-8"), $wordcount, $sent, $sent_html, trim($sent_real), getWordEn($sent_real));
        //echo "end={$end}<br>";
        //echo "<div>[{$iCurrPara}-{$begin}-{$end}]({$wordcount})<br>{$sent}<br>{$sent_real}<br>".getWordEn($sent_real)."</div>";
    }
} else {
    echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
}

// 开始一个事务，关闭自动提交

PDO_Connect("$db_file");
$PDO->beginTransaction();
$query = "INSERT INTO "._TABLE_PALI_SENT_." (book , paragraph , begin , end , length , count , text , html , real , real_en ) VALUES ( ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
$stmt = $PDO->prepare($query);
foreach ($arrSent as $oneParam) {
    $stmt->execute($oneParam);
}
// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    echo "error - $error[2] <br>";

    $log = $log . "$from, $FileName, error, $error[2] \r\n";
} else {
    $count = count($arrSent);
    echo "updata $count recorders.";
}

$myLogFile = fopen(_DIR_LOG_ . "insert_sent.log", "a");
fwrite($myLogFile, $log);
fclose($myLogFile);

?>


<?php

if ($from >= $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"db_insert_sentence.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1) . "——" . $filelist[$from + 1][0];
}
?>
</body>
</html>
