<?php
require_once "../../app/path.php";

$src_db=_SRC_DB_PALI_SENTENCE_;#源数据库
$src_table=_TABLE_PALI_SENT_;#源表名
$dest_db=_FILE_DB_PALI_SENTENCE_;#目标数据库
$dest_table=_TABLE_PALI_SENT_ORG_;#目标表名

echo "migarate pali_sent_org".PHP_EOL;
#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
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

$query = "INSERT INTO ".$dest_table." (id, book , paragraph , word_begin , word_end , length , count , text , html,sim_sents ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? ,?)";
$stmtDEST = $PDO_DEST->prepare($query);

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table." WHERE true ";
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();

while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	#插入目标表
    $stmtDEST->execute(array(
					(int)$srcData["id"],
					(int)$srcData["book"],
					(int)$srcData["paragraph"],
					(int)$srcData["begin"],
					(int)$srcData["end"],
					(int)$srcData["length"],
					(int)$srcData["count"],
					$srcData["text"],
					$srcData["html"],
					$srcData["sim_sents"]
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






