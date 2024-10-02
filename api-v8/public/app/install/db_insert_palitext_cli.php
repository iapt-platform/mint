<?php
/*
生成 巴利原文段落表
 */
require_once __DIR__."/../config.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});
define("_DB_", _PG_DB_PALITEXT_);
define("_TABLE_",_PG_TABLE_PALI_TEXT_);

fwrite(STDOUT, "Insert Pali Text To DB".PHP_EOL);

if ($argc != 3) {
	echo "help".PHP_EOL;
	echo $argv[0]." from to".PHP_EOL;
	echo "from = 1-217".PHP_EOL;
	echo "to = 1-217".PHP_EOL;
	exit;
}
$_from = (int) $argv[1];
$_to = (int) $argv[2];
if ($_to > 217) {
	$_to = 217;
}


$to = $_to;

$filelist = array();
$fileNums = 0;
$log = "";
fwrite(STDOUT, "doing $_from".PHP_EOL);

if (($handle = fopen(__DIR__."/filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to > $fileNums) {
    $to = $fileNums;
}

$dns = _DB_;
$dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

for ($from=$_from-1; $from < $to; $from++) { 
    # code...

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
    fwrite(STDOUT, "doing:" . $xmlfile . PHP_EOL);
    $log = $log . "$from,$FileName,open\r\n";
    
    $arrInserString = array();
    
    
    // 打开vri html文件并读取数据
    $pali_text_array = array();
    if (($fpPaliText = fopen($dirPaliTextBase . $xmlfile, "r")) !== false) {
        while (($data = fgets($fpPaliText)) !== false) {
            if (substr($data, 0, 2) === "<p") {
                array_push($pali_text_array, $data);
            }
    
        }
        fclose($fpPaliText);
        fwrite(STDOUT, "pali text load：" . $dirPaliTextBase . $xmlfile . PHP_EOL);
    } else {
        fwrite(STDERR, "can not pali text file. filename=" . $dirPaliTextBase . $xmlfile.PHP_EOL);
		continue;
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
        fwrite(STDOUT, "单词表load：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv".PHP_EOL);
    } else {
        fwrite(STDERR, "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv".PHP_EOL);
        continue;
    }
    
    if (($inputRow - 1) != count($pali_text_array)) {
        $log = "$from, $FileName,error,文件行数不匹配 inputRow=$inputRow pali_text_array=" . count($pali_text_array) . PHP_EOL;
        fwrite(STDERR, $log);
		continue;
    }
    
    #删除 旧数据
    $query = "DELETE FROM "._TABLE_." WHERE book=?";
	$stmt = $dbh->prepare($query);
	try{
		$stmt->execute(array($from+1));
	}catch(PDOException $e){
		fwrite(STDERR,$e->getMessage());
		continue;
	}

    
    // 开始一个事务，关闭自动提交
    $dbh->beginTransaction();
    
    $query = "INSERT INTO "._TABLE_." ( book , paragraph , level , class , toc , text , html , lenght , created_at,updated_at ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? ,now(),now() )";
    $stmt = $dbh->prepare($query);
    foreach ($arrInserString as $oneParam) {
        if ($oneParam[3] < 100) {
            $toc = $oneParam[6];
        } else {
            $toc = "";
        }
        $newData = array($from + 1, $oneParam[2], $oneParam[3], $oneParam[4], $toc, $oneParam[6], $oneParam[5], mb_strlen($oneParam[6], "UTF-8"));
        try{
			$stmt->execute($newData);
		}catch(PDOException $e){
			fwrite(STDERR,$e->getMessage());
			fwrite(STDERR,implode(",",$newData));
			break;
		}
		
    }
    // 提交更改
    $dbh->commit();
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = $dbh->errorInfo();
        fwrite(STDERR, "error - $error[2]".PHP_EOL);
    
        $log = $log . "$from, $FileName, error, $error[2] \r\n";
    } else {
        $count = count($arrInserString);
        fwrite(STDOUT, "updata $count recorders.".PHP_EOL);
    }
    /*
    $myLogFile = fopen($dirLog . "db_insert_palitext.log", "a");
    fwrite($myLogFile, $log);
    fclose($myLogFile);
    */
}
fwrite(STDOUT, "all done!".PHP_EOL);



?>
