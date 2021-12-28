<?php
/*
导入巴利书名
*/
require_once __DIR__."/../../app/config.php";

define("_PG_DB_PALITEXT_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_PALI_BOOK_NAME_","books");

$src_file=_DIR_PALI_TITLE_."/pali_serieses.csv";#源数据

$dest_db=_PG_DB_PALITEXT_;#目标数据库
$dest_table=_PG_TABLE_PALI_BOOK_NAME_;#目标表名

echo "migarate pali_serieses".PHP_EOL;
#打开源
if (($fp = fopen($src_file, "r")) === FALSE) {
	echo "open $src_file fail".PHP_EOL;
	exit;
}
echo "open src".PHP_EOL;

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo "open dest".PHP_EOL;

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
echo "begin Transaction".PHP_EOL;

$PDO_DEST->beginTransaction();

$query = "INSERT INTO ".$dest_table." (id, book , paragraph , title ) VALUES ( ? , ? , ? , ? )";
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
		$date= array(
			(int)$data[0],
			(int)$data[1],
			(int)$data[2],
			$data[3],
		);
		try{					
			$stmtDEST->execute($data);		
		}catch(PDOException $e){
			fwrite(STDERR,"error:".$e->getMessage().implode(',',$data));
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






