<?php
/*
用拆分好的三藏数据 生成模板库
 */
require_once __DIR__."/../config.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});

fwrite(STDOUT, "Insert templet to DB".PHP_EOL);

if ($argc != 3) {
	echo "help".PHP_EOL;
	echo "db_insert_templet.php from to".PHP_EOL;
	echo "from = 1-217".PHP_EOL;
	echo "to = 1-217".PHP_EOL;
	exit;
}
$_from = (int) $argv[1];
$_to = (int) $argv[2];
if ($_to > 217) {
	$_to = 217;
}


#pg
define("_DB_", _PG_DB_PALICANON_TEMPLET_);
define("_TABLE_",_PG_TABLE_PALICANON_TEMPLET_);


$filelist = array();

if (($handle = fopen(__DIR__."/filelist.csv", 'r')) !== false) {
    while (($filelist[] = fgetcsv($handle, 0, ',')) !== false) {
    }
}
fwrite(STDOUT, "read file list".PHP_EOL);



$dns = _DB_;
$dbh = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT, "db Connectd".PHP_EOL);


for ($from=$_from; $from <=$_to ; $from++) { 
	# code...
	
	$fileSn = $from-1;
	fwrite(STDOUT, "doing:".$from.PHP_EOL);
	$FileName = $filelist[$fileSn][1] . ".htm";
	$fileId = $filelist[$fileSn][0];

	
	$inputFileName = $FileName;
	$outputFileNameHead = $filelist[$fileSn][1];
	$bookId = $filelist[$fileSn][2];
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
	fwrite(STDOUT, "doing:" . $xmlfile . PHP_EOL);
	$log =  "$from,$FileName,open\r\n";
	//fwrite($myLogFile, $log);

	#删除目标数据库中数据
	$query = "DELETE FROM "._TABLE_." WHERE book = ?";
	$stmt = $dbh->prepare($query);
	$stmt->execute(array($from));


	fwrite(STDOUT, "delete ".PHP_EOL);

	$row=0;
	// 开始一个事务，关闭自动提交
	$dbh->beginTransaction();
	$query = "INSERT INTO "._TABLE_." ( book , paragraph, wid , word , real , type , gramma , part , style, created_at,updated_at ) VALUES (?,?,?,?,?,?,?,?,?,now(),now())";
	$stmt = $dbh->prepare($query);
	if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
		$error = $dbh->errorInfo();
		fwrite(STDERR, "error - $error[2]".PHP_EOL);
		$log = "$from, $FileName, error, $error[2] \r\n";
		exit;
	} else {
		// 打开文件并读取数据
		if (($fp = fopen($dirXmlBase . $dirXml . $outputFileNameHead . ".csv", "r")) !== false) {
			while (($data = fgetcsv($fp, 0, ',')) !== false) {
				if($row>0){
					#或略第一行 标题行
					$params = array(
						mb_substr($data[2], 1),
						$data[3],
						$data[16],
						$data[4],
						$data[5],
						$data[6],
						$data[7],
						$data[10],
						$data[15]);		
						try{
							$stmt->execute($params);
						}catch(PDOException $e){
							fwrite(STDERR,$e->getMessage().PHP_EOL);
							fwrite(STDERR,implode(",",$params).PHP_EOL);
							fwrite(STDERR,$e->getLine().PHP_EOL);
							break;
						}
				}

				$row++;
			}
			fclose($fp);
			fwrite(STDOUT, "word loaded：" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv".PHP_EOL);
		} else {
			fwrite(STDERR, "can not open csv file. filename=" . $dirXmlBase . $dirXml . $outputFileNameHead . ".csv".PHP_EOL);
		}
	}

	// 提交更改
	$dbh->commit();
	if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
		$error = $dbh->errorInfo();
		fwrite(STDERR, "error - $error[2]".PHP_EOL);
		$log = "$from, $FileName, error, $error[2] \r\n";
		exit(1);
	} else {
		fwrite(STDOUT, "updata $row recorders.".PHP_EOL);

	}
}

exit();





//fclose($myLogFile);



?>

