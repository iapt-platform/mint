<?php
/*
导入巴利书名
*/
require_once __DIR__."/../../app/config.php";

$src_file=_DIR_PALI_TITLE_."/pali_serieses.csv";#源数据

$dest_db=_FILE_DB_PALITEXT_;#目标数据库
$dest_table=_TABLE_PALI_BOOK_NAME_;#目标表名

echo "migarate pali_serieses".PHP_EOL;
#打开源
if (($fp = fopen($src_file, "r")) === FALSE) {
	echo "open $src_file fail".PHP_EOL;
	exit;
}
echo "open src".PHP_EOL;

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
echo "open dest".PHP_EOL;

#删除目标表中所有数据
$query = "DELETE FROM ".$dest_table." WHERE true";
$stmt = $PDO_DEST->prepare($query);
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = $PDO_DEST->errorInfo();
    echo "error - $error[2] ";
	exit;
}

$stmt->execute();
echo "delete dest".PHP_EOL;

// 开始一个事务，关闭自动提交
$count = 0;
echo "begin Transaction".PHP_EOL;

$PDO_DEST->beginTransaction();

$query = "INSERT INTO ".$dest_table." (id, book , paragraph , title ) VALUES ( ? , ? , ? , ? )";
$stmtDEST = $PDO_DEST->prepare($query);

#从源数据表中读取
$row=0;

while (($data = fgetcsv($fp, 0, ',')) !== false){
	if($row>0){
		#插入目标表
		$stmtDEST->execute(array(
			(int)$data[0],
			(int)$data[1],
			(int)$data[2],
			$data[3],
		));
		if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
			$error = $PDO_DEST->errorInfo();
			echo "error - $error[2] ";
			exit;
		}
	}	
	$row++;
}
fclose($fp);

// 提交更改
$PDO_DEST->commit();
echo "done".PHP_EOL;






