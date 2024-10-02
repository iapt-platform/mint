<?php
/*
导入巴利书名
*/
require_once __DIR__."/../../../public/app/config.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});

$src_file=_DIR_PALI_TITLE_."/pali_serieses.csv";#源数据

$dest_db=_PG_DB_PALITEXT_;#目标数据库
$dest_table=_PG_TABLE_PALI_BOOK_NAME_;#目标表名

fwrite(STDOUT, "migarate pali_serieses".PHP_EOL);
#打开源
if (($fp = fopen($src_file, "r")) === FALSE) {
	fwrite(STDERR, "open $src_file fail".PHP_EOL);
	exit;
}
fwrite(STDOUT, "open src".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT, "open dest".PHP_EOL);

#删除目标表中所有数据
fwrite(STDOUT,"deleting date".PHP_EOL) ;
try{
	$query = "DELETE FROM ".$dest_table;
	$stmt = $PDO_DEST->prepare($query);
	$stmt->execute();
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}
fwrite(STDOUT,"deleted date".PHP_EOL) ;

// 开始一个事务，关闭自动提交
$count = 0;
fwrite(STDOUT, "begin Transaction".PHP_EOL);

$PDO_DEST->beginTransaction();

$query = "INSERT INTO ".$dest_table." ( book , paragraph , title ) VALUES (  ? , ? , ? )";
try{
	$stmtDEST = $PDO_DEST->prepare($query);
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}


#从源数据表中读取
$row=0;
$count = 0;
while (($data = fgetcsv($fp, 0, ',')) !== false){
	if($row>0){
		#插入目标表
		$rowData= array(
			$data[1],
			$data[2],
			$data[3]
		);
		try{					
			$stmtDEST->execute($rowData);		
		}catch(PDOException $e){
			fwrite(STDERR,"error:".$e->getMessage().PHP_EOL);
			fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
			fwrite(STDERR,"error-data:".implode(',',$rowData).PHP_EOL);
			exit;
		}
		$count++;
	}	
	$row++;
}
fclose($fp);

// 提交更改
$PDO_DEST->commit();
echo "done $count row".PHP_EOL;






