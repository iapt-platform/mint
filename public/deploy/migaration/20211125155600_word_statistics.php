<?php
require_once __DIR__."/../../app/config.php";

define("_PG_DB_STATISTICS_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_WORD_STATISTICS_", "word_statistics");

#打开源数据库
$PDO_SRC = new PDO(_SRC_DB_STATISTICS_,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
echo "open src".PHP_EOL;

#打开目标数据库
$PDO_DEST = new PDO(_PG_DB_STATISTICS_,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open dest ok".PHP_EOL) ;

#删除目标数据库中所有数据
fwrite(STDOUT,"deleting date".PHP_EOL) ;

try{
	$query = "DELETE FROM "._PG_TABLE_WORD_STATISTICS_;
	$stmt = $PDO_DEST->prepare($query);
	$stmt->execute();
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}
fwrite(STDOUT,"deleted date".PHP_EOL) ;


// 开始一个事务，关闭自动提交
$count = 0;
fwrite(STDOUT,"begin Transaction".PHP_EOL);

$PDO_DEST->beginTransaction();

$query = "INSERT INTO "._PG_TABLE_WORD_STATISTICS_." ( bookid , word , count , base , end1 , end2 , type , length ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? )";
try{
	$stmtDEST = $PDO_DEST->prepare($query);
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}
#从源数据库中读取
$query = "SELECT *  FROM "._SRC_TABLE_WORD_STATISTICS_." WHERE true ";

try{
	$stmtSrc = $PDO_SRC->prepare($query);
	$stmtSrc->execute();
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	#插入目标表
		$data = array(
						$srcData["bookid"],
						$srcData["word"],
						(int)$srcData["count"],
						$srcData["base"],
						$srcData["end1"],
						$srcData["end2"],
						(int)$srcData["type"],
						(int)$srcData["length"]
					);
	try{					
		$stmtDEST->execute($data);		
	}catch(PDOException $e){
		fwrite(STDERR,"error:".$e->getMessage().implode(',',$data));
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






