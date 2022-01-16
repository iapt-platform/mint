<?php
require_once __DIR__."/../config.php";
require_once __DIR__."/../public/function.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});

define("_DB_RES_INDEX_", _PG_DB_RESRES_INDEX_);
define("_TABLE_",_PG_TABLE_RES_INDEX_);

fwrite(STDOUT, "Update toc to res_index".PHP_EOL);

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
fwrite(STDOUT, "doing $_from".PHP_EOL);

if (($handle = fopen(__DIR__."/filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to >= $fileNums) {
    $to = $fileNums - 1;
}


$dns = _DB_RES_INDEX_;
$dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
        fwrite(STDOUT, "res load：" . _DIR_PALI_TITLE_ . "/" . ($from + 1) . "_title.csv".PHP_EOL);
    } else {
        fwrite(STDOUT, "can not open csv ".PHP_EOL);
        continue;
    }
    
    $book = $from + 1;
    
    //删除已有标题
    
    $query = "DELETE FROM \""._TABLE_."\" WHERE book = ?  AND  language = ?  ";
	try{
		$stmt = $dbh->prepare($query);
		$stmt->execute(array($book,$_lang));
	}catch(PDOException $e){
		fwrite(STDERR,"error:".$e->getMessage());
		continue;
	}
    
    // 开始一个事务，关闭自动提交
    $dbh->beginTransaction();
    $query = "INSERT INTO \""._TABLE_."\" (book , paragraph, title, title_en , level, type , language , author , share , create_time , update_time,created_at,updated_at  ) VALUES (  ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? ,now(),now())";
    try{
	$stmt = $dbh->prepare($query);
	}catch(PDOException $e){
		fwrite(STDERR,"error:".$e->getMessage());
		break;
	}
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
		try{
			$stmt->execute($newData);
		}catch(PDOException $e){
			fwrite(STDERR,"error:".$e->getMessage());
			fwrite(STDERR,implode(",",$newData));
			break;
		}
    }
    // 提交更改
    $dbh->commit();
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = $dbh->errorInfo();
        fwrite(STDERR, "error - $error[2] ".PHP_EOL);
        $log = $log . "$from, error, $error[2] \r\n";
    } else {
        $count = count($arrInserString);
        fwrite(STDOUT, "updata $count title recorders.".PHP_EOL);
    }
    //段落信息结束
    /*
    $myLogFile = fopen(_DIR_LOG_ . "/db_update_toc.log", "a");
    fwrite($myLogFile, $log);
    fclose($myLogFile);
    */
}
fwrite(STDOUT, "齐活！功德无量！all done!".PHP_EOL);



function getWordEn($strIn)
{
    $strIn = strtolower($strIn);
    $search = array('ā', 'ī', 'ū', 'ṅ', 'ñ', 'ṭ', 'ḍ', 'ṇ', 'ḷ', 'ṃ');
    $replace = array('a', 'i', 'u', 'n', 'n', 't', 'd', 'n', 'l', 'm');
    return (str_replace($search, $replace, $strIn));
}
?>

