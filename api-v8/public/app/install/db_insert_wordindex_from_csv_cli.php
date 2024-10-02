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

//PostgreSQL
define("_DB_", _PG_DB_WORD_INDEX_);
define("_TABLE_", _PG_TABLE_WORD_INDEX_);

fwrite(STDOUT, "Insert Word Index To DB".PHP_EOL);


$dirLog = _DIR_LOG_ . "/";
$log = "";



$dns = _DB_;
$dbh_word_index = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_word_index->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#删除
try{
	$query = "DELETE FROM "._TABLE_;
	$stmt = $dbh_word_index->prepare($query);
	$stmt->execute();
}catch(PDOException $e){
	fwrite(STDERR,$e->getMessage());
	exit;
}


    $scan = scandir(_DIR_CSV_PALI_CANON_WORD_INDEX_);
    foreach($scan as $filename) {
        $filename = _DIR_CSV_PALI_CANON_WORD_INDEX_."/".$filename;
        if (is_file($filename)) {
            fwrite(STDOUT, "doing ".$filename.PHP_EOL);
            if (($fpoutput = fopen($filename, "r")) !== false) {

                // 开始一个事务，关闭自动提交
                $dbh_word_index->beginTransaction();
                $query = "INSERT INTO "._TABLE_." (id , word , word_en , count , normal , bold , is_base , len , created_at,updated_at ) 
				                            VALUES ( ?, ?, ?, ?, ?, ?, ?, ? , now(), now())";
				try{
					$stmt = $dbh_word_index->prepare($query);
				}catch(PDOException $e){
					fwrite(STDERR,"error:".$e->getMessage()." At Line: ".$e->getLine().PHP_EOL);
					exit;
				}
                
        
                $count = 0;
                while (($data = fgetcsv($fpoutput, 0, ',')) !== false) {
					try{
						$stmt->execute($data);
					}catch(PDOException $e){
						fwrite(STDERR,"error:".$e->getMessage()." At Line: ".$e->getLine().PHP_EOL);
						fwrite(STDERR,"error-data:".implode(",",$data).PHP_EOL);
						fwrite(STDERR,"error-data-count:".count($data).PHP_EOL);
						exit;
					}
                    
                    $count++;
                }
                // 提交更改
                $dbh_word_index->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = $dbh_word_index->errorInfo();
                    fwrite(STDERR, "error - $error[2]".PHP_EOL);
                    $log .= "$filename, error, $error[2] \r\n";
                } else {
                    fwrite(STDOUT, "updata $count recorders.".PHP_EOL);
                    $log .= "updata $count recorders.\r\n";
                }
            }else{
                fwrite(STDERR, "open file error".PHP_EOL);
            }
        
        }
    }

	fwrite(STDOUT, "齐活！功德无量！all done!".PHP_EOL);



?>

