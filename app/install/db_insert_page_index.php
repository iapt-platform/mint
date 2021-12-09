<?php
//require_once "install_head.php";
require_once "./_pdo.php";
require_once "../config.php";

if (PHP_SAPI == "cli") {
    echo $argc;
    if ($argc >= 3) {
        $from = $argv[1];
        $to = $argv[2];
        echo "From: {$from} To:{$to}";
    } else if ($argc >= 1) {
        $from = 0;
        $to = 216;
        echo "生成全部217本书";
    } else {
        echo "参数错误";
        exit;
    }

} else {
    echo "<!DOCTYPE html><html><head></head>";
    echo "<body><h2>Insert to Index</h2>";

    if (isset($_GET["from"]) == false) {
        echo '<form action="db_insert_index_once.php" method="get">';
        echo 'From: <input type="text" name="from" value="0"><br>';
        echo 'To: <input type="text" name="to" value="216"><br>';
        echo '<input type="submit">';
        echo '</form>';
        exit;
    } else {
        $from = $_GET["from"];
        $to = $_GET["to"];
    }
}

$g_wordCounter = 0;
$g_wordIndexCounter = 0;
$iAllWordIndex = array();
$sAllWord = array();

$dirLog = _DIR_LOG_ . "/";

$dirXmlBase = _DIR_PALI_CSV_ . "/";

$filelist = array();
$fileNums = 0;
$log = "";
echo "\n <h2>$from</h2>";

if (($handle = fopen("filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to >= $fileNums) {
    $to = $fileNums - 1;
}

for ($iFile = $from; $iFile <= $to; $iFile++) {
    echo "<h3>{$iFile}</h3>";
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

    $db_file = _FILE_DB_PAGE_INDEX_;
    PDO_Connect("$db_file");
    // 打开文件并读取数据
    $irow = 0;
    if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
        // 开始一个事务，关闭自动提交
        $PDO->beginTransaction();
        $query = "INSERT INTO m ('book','para','page1','page2') VALUES (?,?,?,?)";
        $stmt = $PDO->prepare($query);

        $currPage = array(0, 0);
        $currPara = 0;
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            $irow++;
            if ($irow > 1) {
                if ($data[6] == ".ctl." && mb_substr($data[5], 0, 1, "UTF-8") == "M") {
                    $sPage = mb_substr($data[5], 1, 6, "UTF-8");
                    $aPage = explode(".", $sPage);
                    if (count($aPage) == 2) {
                        $currPage = $aPage;
                    } else {
                        echo "错误的页码: {$data[5]} \n ";
                    }
                }
                $para = $data[3];
                if ($currPara != $data[3]) {
                    $currPara = (int) $data[3];
                    $book = mb_substr($data[2], 1, null, "UTF-8");
                    $para = $currPara;
                    $page1 = $currPage[0];
                    $page2 = $currPage[1];
                    $stmt->execute(array($book, $para, $page1, $page2));
                }
            }

        }

        // 提交更改
        $PDO->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            echo "error - $error[2] <br>";
            $log .= "$from, $FileName, error, $error[2] \r\n";
        } else {
            echo "updata  recorders.<br />";
            $log .= "updata  recorders.\r\n";
        }
        fclose($fp);
        echo "单词表load：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv<br>\n";
    } else {
        echo "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv";
    }
}
echo "齐活！功德无量！all done!";
?>



</body>
</html>
