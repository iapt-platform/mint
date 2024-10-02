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


$dirLog = _DIR_LOG_ . "/";
$dirXmlBase = _DIR_PALI_CSV_ . "/";

$filelist = array();
$fileNums = 0;
$log = "";

//PostgreSQL
define("_DB_",_PG_DB_BOOK_WORD_);
define("_TABLE_", _PG_TABLE_BOOK_WORD_);

global $dbh_word_index;
$dns = _DB_;
$dbh_word_index = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_word_index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if (($handle = fopen(__DIR__."/filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($_to == 0 || $_to >= $fileNums) {
    $_to = $fileNums ;
}

for ($from=$_from-1; $from < $_to; $from++) { 
    fwrite(STDOUT, "doing ".($from+1).PHP_EOL);

    $bookword = array();

    if (($fpoutput = fopen(_DIR_CSV_PALI_CANON_WORD_ . "/{$from}_words.csv", "r")) !== false) {
        $count = 0;
        while (($data = fgetcsv($fpoutput, 0, ',')) !== false) {
            $book = $data[1];
            if (isset($bookword[$data[3]])) {
                $bookword[$data[3]]++;
            } else {
                $bookword[$data[3]] = 1;
            }

            $count++;
        }
    }
    #删除原来的数据
    $query = "DELETE FROM "._TABLE_." WHERE book = ?";
    $stmt = $dbh_word_index->prepare($query);
	try{
		$stmt->execute(array($book));
	}catch(PDOException $e){
		fwrite(STDERR,"error:".$e->getMessage());
		continue;
	}
    
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = $dbh_word_index->errorInfo();
        fwrite(STDERR, "error - $error[2]".PHP_EOL);
        $log .= "$from, $FileName, error, $error[2] \r\n";
    }else{
        // 开始一个事务，关闭自动提交
        $dbh_word_index->beginTransaction();
        $query = "INSERT INTO "._TABLE_." (book , wordindex , count , created_at,updated_at) VALUES ( ? , ? , ? , now(),now() )";
        $stmt = $dbh_word_index->prepare($query);

        foreach ($bookword as $key => $value) {
			try{
			$stmt->execute(array($book, $key, $value));	
			}catch(PDOException $e){
				fwrite(STDERR,$e->getMessage());
			}
            
        }
        // 提交更改
        $dbh_word_index->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = $dbh_word_index->errorInfo();
            fwrite(STDERR, "error - $error[2]".PHP_EOL);
            $log .= "$from, $FileName, error, $error[2] \r\n";
        } else {
            fwrite(STDOUT, "updata $count recorders.".PHP_EOL);
            $log .= "updata $count recorders.\r\n";
        }	
    }

    /*
    $myLogFile = fopen($dirLog . "insert_index.log", "a");
    fwrite($myLogFile, $log);
    fclose($myLogFile);
    */
}
fwrite(STDOUT, "齐活！功德无量！all done!".PHP_EOL);

?>
