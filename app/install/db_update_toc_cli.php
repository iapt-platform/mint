<?php
require_once __DIR__."/../config.php";
require_once __DIR__.'/../public/_pdo.php';
require_once __DIR__."/../public/function.php";

echo "Update toc to res_index".PHP_EOL;

if ($argc != 4) {
	echo $argv[0]." help".PHP_EOL;
	echo " from to language".PHP_EOL;
	echo "from = 1-217".PHP_EOL;
	echo "to = 1-217".PHP_EOL;
	echo "language = pali/zh-hans/zh-hant".PHP_EOL;
	exit;
}
$_from = (int) $argv[1];
$_to = (int) $argv[2];
if ($_to > 217) {
	$_to = 217;
}

$to = $_to;
$_file = $argv[3];
$_lang = $argv[3];
$filelist = array();
$fileNums = 0;
$log = "";
echo "doing $_from".PHP_EOL;

if (($handle = fopen(__DIR__."/filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to >= $fileNums) {
    $to = $fileNums - 1;
}

PDO_Connect(_FILE_DB_RESRES_INDEX_);

for ($from=$_from-1; $from < $_to; $from++) { 
    # code...

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
    $strFileName = _DIR_PALI_TITLE_ . "/" . ($from + 1) . "_{$_file}.csv";
    if(!file_exists($strFileName)){
        continue;
    }
    $inputRow = 0;
    if (($fp = fopen($strFileName, "r")) !== false) {
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if ($inputRow > 0 && $data[3] != 100 && !empty($data[6])) {
                array_push($arrInserString, $data);
            }
            $inputRow++;
        }
        fclose($fp);
        echo "res load：" . _DIR_PALI_TITLE_ . "/" . ($from + 1) . "_title.csv".PHP_EOL;
    } else {
        echo "can not open csv ";
        continue;
    }
    
    $book = $from + 1;
    
    //删除已有标题
    
    $query = "DELETE FROM "._TABLE_RES_INDEX_." WHERE book = ?  AND  language = ?  ";
    PDO_Execute($query, array($book,$_lang));
    
    
    // 开始一个事务，关闭自动提交
    $PDO->beginTransaction();
    $query = "INSERT INTO "._TABLE_RES_INDEX_." (book , paragraph, title, title_en , level, type , language , author , share , create_time , update_time  ) VALUES (  ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )";
    $stmt = $PDO->prepare($query);
    if ($_lang == "pali") {
        $type = 1;
    } else {
        $type = 2;
    }
    foreach ($arrInserString as $title) {
        if (isset($title[7])) {
            $author = $title[7];
        }else {
            $author = "cscd4";
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
        echo "error - $error[2] ".PHP_EOL;
        $log = $log . "$from, error, $error[2] \r\n";
    } else {
        $count = count($arrInserString);
        echo "updata $count title recorders.".PHP_EOL;
    }
    //段落信息结束
    /*
    $myLogFile = fopen(_DIR_LOG_ . "/db_update_toc.log", "a");
    fwrite($myLogFile, $log);
    fclose($myLogFile);
    */
}
 echo "<h2>齐活！功德无量！all done!</h2>";



function getWordEn($strIn)
{
    $strIn = strtolower($strIn);
    $search = array('ā', 'ī', 'ū', 'ṅ', 'ñ', 'ṭ', 'ḍ', 'ṇ', 'ḷ', 'ṃ');
    $replace = array('a', 'i', 'u', 'n', 'n', 't', 'd', 'n', 'l', 'm');
    return (str_replace($search, $replace, $strIn));
}
?>

