<?php
require_once __DIR__."/../../app/path.php";

#打开源数据库
$PDO_SRC = new PDO(_SRC_DB_STATISTICS_,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
echo "open src".PHP_EOL;

#打开目标数据库
$PDO_DEST = new PDO(_FILE_DB_STATISTICS_,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
echo "open dest".PHP_EOL;

#删除目标数据库中所有数据
$query = "DELETE FROM "._TABLE_WORD_STATISTICS_." WHERE true";
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

$query = "INSERT INTO "._TABLE_WORD_STATISTICS_." ( bookid , word , count , base , end1 , end2 , type , length ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )";
$stmtDEST = $PDO_DEST->prepare($query);

#从源数据库中读取
$query = "SELECT *  FROM "._SRC_TABLE_WORD_STATISTICS_." WHERE true ";
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();

while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	#插入目标表
    $stmtDEST->execute(array(
					$srcData["bookid"],
					$srcData["word"],
					(int)$srcData["count"],
					$srcData["base"],
					$srcData["end1"],
					$srcData["end2"],
					(int)$srcData["type"],
					(int)$srcData["length"]
				));
	if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
		$error = $PDO_DEST->errorInfo();
		echo "error - $error[2] ";
		exit;
	}			
	$count++;
	if($count%10000==0){
		echo "finished $count".PHP_EOL;
	}
}

// 提交更改
$PDO_DEST->commit();
echo "done".PHP_EOL;






