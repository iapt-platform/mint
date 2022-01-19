<?php
/*
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
require_once __DIR__."/../../app/config.php";

# 更新索引表
$src_db=_FILE_SRC_WBW_;#源数据库
$src_table=_TABLE_SRC_DICT_WBW_;#源表名
$dest_db=_FILE_DB_WBW_;#目标数据库
$dest_table=_TABLE_DICT_WBW_;#目标表名

echo "migarate user dict".PHP_EOL;
#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
echo "open src".PHP_EOL;

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
echo "open dest".PHP_EOL;

#删除源数据表中全部数据
fwrite(STDOUT,"delete dest".PHP_EOL);

$query = "delete from $dest_table where true;";
$stmtDest = $PDO_DEST->prepare($query);
$stmtDest->execute();

$queryInsert = "INSERT INTO ".$dest_table." 
								(
									word , 
									type, 
									gramma,
									parent,
									mean,
									note,
									factors,
									factormean,
									status,
									source,
									language,
									confidence,
									creator_id,
									ref_counter,
									create_time,
									created_at,
									updated_at
								) 
									VALUES ( ? , ? , ? ,? ,? ,? ,? ,? ,? ,? , ?,?,?,?,?,to_timestamp(?),to_timestamp(?))";

echo "read from orginal".PHP_EOL;
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
	
	//if(strlen($srcData["id"])>10 && strlen($srcData["owner"])>30)
	{

		{
			#没有相同数据
			$commitData[] = array(
					$srcData["pali"],
					$srcData["type"],
					$srcData["gramma"],
					$srcData["parent"],
					$srcData["mean"],
					$srcData["note"],
					$srcData["factors"],
					$srcData["factormean"],
					(int)$srcData["status"],
					"_SYS_USER_WBW_",
					$srcData["language"],
					(int)$srcData["confidence"],
					(int)$srcData["creator"],
					$srcData["ref_counter"],
					$srcData["time"],
					$srcData["time"],
					$srcData["time"]
				);	
			$count++;	
			$allInsertCount++;
		}

		if($count ==10000){
			#10000行插入一次
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

# 更新索引表

$src_table=_TABLE_SRC_DICT_WBW_INDEX_;#源表名
$src_word_table=_TABLE_SRC_DICT_WBW_;#源word表名

echo "migarating usr dict index ".PHP_EOL;

$queryInsert = "INSERT INTO ".$dest_table." 
								(
									word , 
									type, 
									gramma,
									parent,
									mean,
									note,
									factors,
									factormean,
									status,
									source,
									language,
									confidence,
									creator_id,
									ref_counter,
									create_time,
									created_at,
									updated_at
								) 
									VALUES ( ? , ? , ? ,? ,? ,? ,? ,? ,? ,? , ?,?,?,?,?, to_timestamp(?), to_timestamp(?))";


// 开始一个事务，关闭自动提交
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
	$wordIndex = $srcData["word_index"];
	#查询目标表中的数据
	$queryExsit = "SELECT *  FROM ".$src_word_table." WHERE id = ? ";
	$getWord = $PDO_SRC->prepare($queryExsit);
	$getWord->execute(array($wordIndex));
	$exist = $getWord->fetch(PDO::FETCH_ASSOC);
	if($exist){
		$commitData[] = array(
			$exist["pali"],
			$exist["type"],
			$exist["gramma"],
			$exist["parent"],
			$exist["mean"],
			$exist["note"],
			$exist["factors"],
			$exist["factormean"],
			(int)$exist["status"],
			"_USER_WBW_",
			$exist["language"],
			(int)$exist["confidence"],
			(int)$srcData["user_id"],
			$exist["ref_counter"],
			$exist["time"],
			$exist["time"],
			$exist["time"]
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
	}else{
		fwrite(STDERR,"error: no word index - $wordIndex".PHP_EOL);
	}
	if($allSrcCount % 10000 ==0){
		fwrite(STDOUT,"find from src table $allSrcCount / $allInsertCount is new.".PHP_EOL) ;
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
			fwrite(STDOUT,"error - $error[2] ");
			exit;
		}	
	}
	// 提交更改
	$PDO_DEST->commit();
	fwrite(STDOUT,"finished $count".PHP_EOL);
}

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL);

fwrite(STDOUT,"all done".PHP_EOL);






