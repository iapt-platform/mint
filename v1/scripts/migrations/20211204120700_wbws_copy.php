<?php
/*
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
// Require Composer's autoloader.
require_once __DIR__.'/../../vendor/autoload.php';
require_once __DIR__."/../../app/config.php";
require_once __DIR__."/../../env.php";


# 雪花id
$snowflake = new SnowFlakeId();



#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名

# 更新数据表
$src_db=_SRC_DB_USER_WBW_;#源数据库
$src_table=_TABLE_SRC_USER_WBW_;#源表名

$dest_db=_FILE_DB_USER_WBW_;#目标数据库
$dest_table=_TABLE_USER_WBW_;#目标表名

echo "migarating wbw".PHP_EOL;

// 开始一个事务，关闭自动提交


$queryInsert = "INSERT INTO ".$dest_table." (
									  id,
	                                  uid, 
									  block_uid , 
									  book_id, 
									  paragraph,
									  wid,
									  word,
									  data,
									  status,
									  creator_uid,
									  create_time,
									  modify_time,
									  created_at,
									  updated_at) VALUES ( ?,? , ? , ? , ? ,? ,? ,? ,? ,? ,? ,?,to_timestamp(?),to_timestamp(?))";
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
	$uuid = $srcData["id"];
	#查询目标表中是否有相同数据
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($uuid));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if(!$exist){
		#没有相同的数据就插入
		$commitData[] = array(
			$snowflake->id(),
			$uuid,
			$srcData["block_id"],
			$srcData["book"],
			$srcData["paragraph"],
			$srcData["wid"],
			$srcData["word"],
			$srcData["data"],
			$srcData["status"],
			$srcData["owner"],
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
			$commitData = [];
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






