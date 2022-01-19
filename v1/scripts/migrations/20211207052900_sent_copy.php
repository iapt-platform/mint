<?php
/*
迁移 sentence库
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
require_once __DIR__."/../../app/config.php";

# 更新索引表
$src_db=_SRC_DB_SENTENCE_;#源数据库
$src_table=_TABLE_SRC_SENTENCE_BLOCK_;#源表名
$dest_db=_FILE_DB_SENTENCE_;#目标数据库
$dest_table=_TABLE_SENTENCE_BLOCK_;#目标表名

fwrite(STDOUT,"migarate sent_block".PHP_EOL);
#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
fwrite(STDOUT,"open dest table".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." (uid, 
									parent_uid , 
									book_id,
									paragraph,
									owner_uid,
									lang,
									author,
									editor_uid,
									status,
									create_time,
									modify_time,
									created_at,
									updated_at) 
									VALUES ( ? , ? , ? , ? ,? ,? ,? ,? ,? ,? ,?,to_timestamp(?),to_timestamp(?))";


$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table." WHERE true ";
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	#插入目标表

	if(substr($srcData["book"],0,1)==="p"){
		$srcData["book"] = (int)substr($srcData["book"],1);
	}
	
	if(strlen($srcData["id"])>10 && strlen($srcData["owner"])>30){
		$uuid = strtolower(trim($srcData["id"],"{}"));
		#查询目标表中是否有相同数据
		$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
		$getExist = $PDO_DEST->prepare($queryExsit);
		$getExist->execute(array($uuid));
		$exist = $getExist->fetch(PDO::FETCH_ASSOC);
		if(!$exist){
			#没有相同数据
			if(strlen($srcData["editor"])>36){
				fwrite(STDERR,"error: {$uuid} editor {$srcData["editor"]} is too long".PHP_EOL);
				continue;
			}
			$commitData[] = array(
					$uuid,
					trim($srcData["parent_id"],"{}"),
					(int)$srcData["book"],
					(int)$srcData["paragraph"],
					strtolower(trim($srcData["owner"],"{}")),
					$srcData["lang"],
					$srcData["author"],
					$srcData["editor"],
					(int)$srcData["status"],
					$srcData["modify_time"],
					$srcData["modify_time"],
					$srcData["modify_time"]/1000,
					$srcData["modify_time"]/1000
				);	
			$count++;	
			$allInsertCount++;
		}

		if($count ==10000){
			#10000行插入一次
			// 开始一个事务，关闭自动提交
			$PDO_DEST->beginTransaction();
			$stmtDEST = $PDO_DEST->prepare($queryInsert);
			foreach ($commitData as $key => $value) {
				$stmtDEST->execute($value);
				if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
					$error = $PDO_DEST->errorInfo();
					echo "error - $error[2] ";
					exit;
				}
			}
			// 提交更改
			$PDO_DEST->commit();
			$commitData = [];
			echo "finished $count".PHP_EOL;
			$count=0;
		}	
	}
}
if($count>0){
	#最后的没有到10000的数据插入
	$PDO_DEST->beginTransaction();
	$stmtDEST = $PDO_DEST->prepare($queryInsert);
	foreach ($commitData as $key => $value) {
		$stmtDEST->execute($value);
		if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
			$error = $PDO_DEST->errorInfo();
			echo "error - $error[2] ";
			exit;
		}
	}
	// 提交更改
	$PDO_DEST->commit();
	$commitData = [];
	echo "finished $count".PHP_EOL;
}

echo "insert done $allInsertCount in $allSrcCount ".PHP_EOL;

# 更新数据表

$src_db=_SRC_DB_USER_WBW_;#源数据库
$src_table=_TABLE_SRC_SENTENCE_;#源表名
$dest_db=_FILE_DB_USER_WBW_;#目标数据库
$dest_table=_TABLE_SENTENCE_;#目标表名

echo "migarating wbw".PHP_EOL;

// 开始一个事务，关闭自动提交


$queryInsert = "INSERT INTO ".$dest_table." (
	                                  uid, 
									  parent_uid,
									  block_uid , 
									  channel_uid,
									  book_id, 
									  paragraph,
									  word_start,
									  word_end,
									  author,
									  editor_uid,
									  content,
									  language,
									  version,
									  status,
									  strlen,
									  create_time,
									  modify_time,
									  created_at,
									  updated_at) VALUES ( ? , ? , ? , ? ,? ,? ,? ,? ,? ,? ,?,?,?,?,?,?,?,to_timestamp(?),to_timestamp(?))";
$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;
#从源数据表中读取
$query = "SELECT *  FROM ".$src_table." WHERE true ";
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();

while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	#插入目标表
	$uuid = strtolower(trim($srcData["id"],"{}"));
	#查询目标表中是否有相同数据
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($uuid));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if(!$exist){
		if(strlen($srcData["editor"])>36){
			fwrite(STDERR,"error: {$uuid} editor {$srcData["editor"]} too long".PHP_EOL);
			continue;
		}
		$commitData[] = array(
			$uuid,
			$srcData["parent"],
			$srcData["block_id"],
			$srcData["channal"],
			(int)$srcData["book"],
			(int)$srcData["paragraph"],
			(int)$srcData["begin"],
			(int)$srcData["end"],
			$srcData["author"],
			$srcData["editor"],
			$srcData["text"],
			$srcData["language"],
			(int)$srcData["ver"],
			(int)$srcData["status"],
			(int)$srcData["strlen"],
			$srcData["create_time"],
			$srcData["modify_time"],
			$srcData["create_time"]/1000,
			$srcData["modify_time"]/1000
		);
		$count++;
		$allInsertCount++;
		if($count === 10000){
			$PDO_DEST->beginTransaction();
			$stmtDEST = $PDO_DEST->prepare($queryInsert);
			foreach ($commitData as $key => $value) {
				# code...
				$stmtDEST->execute($value);
				if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
					$error = $PDO_DEST->errorInfo();
					echo "error - $error[2] ";
					exit;
				}	
			}
			// 提交更改
			$PDO_DEST->commit();
			echo "finished $count".PHP_EOL;
			$count = 0;
		}
	}
	if($allSrcCount % 10000 ==0){
		echo "find from src table $allSrcCount / $allInsertCount is new.".PHP_EOL;
	}
}
if($count>0){
	#最后的没有到10000的数据插入
	$PDO_DEST->beginTransaction();
	$stmtDEST = $PDO_DEST->prepare($queryInsert);
	foreach ($commitData as $key => $value) {
		# code...
		$stmtDEST->execute($value);
		if (!$stmtDEST || ($stmtDEST && $stmtDEST->errorCode() != 0)) {
			$error = $PDO_DEST->errorInfo();
			echo "error - $error[2] ";
			exit;
		}	
	}
	// 提交更改
	$PDO_DEST->commit();
	echo "finished $count".PHP_EOL;
}

echo "insert done $allInsertCount in $allSrcCount ".PHP_EOL;

echo "all done".PHP_EOL;






