<?php
require_once __DIR__."/../../app/config.php";

define("_PG_DB_PALI_SENTENCE_", _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";");
define("_PG_TABLE_PALI_SENT_ORG_", "pali_sent_orgs");

$src_db=_SRC_DB_PALI_SENTENCE_;#源数据库
$src_table=_TABLE_SRC_PALI_SENT_;#源表名

$dest_db=_PG_DB_PALI_SENTENCE_;#目标数据库
$dest_table=_PG_TABLE_PALI_SENT_ORG_;#目标表名

echo "migarate pali_sent_org".PHP_EOL;
#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
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

$query = "INSERT INTO ".$dest_table." (id, book , paragraph , word_begin , word_end , length , count , text , html,sim_sents ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? ,?)";
try{
	$stmtDEST = $PDO_DEST->prepare($query);
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table;
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






