<?php
/*
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
// Require Composer's autoloader.
require_once __DIR__.'/../../../public/vendor/autoload.php';
require_once __DIR__."/../../../public/app/config.php";
require_once __DIR__."/../../../public/app/public/snowflakeid.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});
$start = time();
# 雪花id
$snowflake = new SnowFlakeId();



#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名

# 更新数据表
$src_db=_SQLITE_DB_USER_WBW_;#源数据库
$src_table=_SQLITE_TABLE_USER_WBW_;#源表名

$dest_db=_PG_DB_USER_WBW_;#目标数据库
$dest_table=_PG_TABLE_USER_WBW_;#目标表名

fwrite(STDOUT,"migarating wbw".PHP_EOL) ;

#打开user数据库
$PDO_USER = new PDO($user_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_USER->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open user table".PHP_EOL);



#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT, "open src".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT, "open dest".PHP_EOL);

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
									  editor_id,
									  create_time,
									  modify_time,
									  created_at,
									  updated_at) VALUES ( ? , ? , ? , ? , ? , ? ,? ,? ,? ,? ,? ,? ,?,to_timestamp(?),to_timestamp(?))";
$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;
#从源数据表中读取
$query = "SELECT *  FROM ".$src_table;
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();


#从user数据表中读取
$query = "SELECT id ,userid FROM ".$user_table." WHERE userid = ? or username = ? ";
$stmtUser = $PDO_USER->prepare($query);

while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	if($srcData["owner"]=='test6'){
		$srcData["owner"] = 'f81c7140-64b4-4025-b58c-45a3b386324a';
	}
	if($srcData["owner"]=='test28'){
		$srcData["owner"] = 'df0ad9bc-c0cd-4cd9-af05-e43d23ed57f0';
	}
	if($srcData["owner"]=='290fd808-2f46-4b8c-b300-0367badd67ed'){
		$srcData["owner"] = 'f81c7140-64b4-4025-b58c-45a3b386324a';
	}
	if($srcData["owner"]=='BA837178-9ABD-4DD4-96A0-D2C21B756DC4'){
		$srcData["owner"] = 'ba5463f3-72d1-4410-858e-eadd10884713';
	}
	$stmtUser->execute(array($srcData["owner"],$srcData["owner"]));
	$userId = $stmtUser->fetch(PDO::FETCH_ASSOC);
	if(!$userId){
		fwrite(STDERR,"no user id {$srcData["owner"]}".PHP_EOL);
		continue;
	}

	#插入目标表
	$uuid = $srcData["id"];

	if(empty($srcData["book"])){
		fwrite(STDERR,"book is null {$uuid}".PHP_EOL);
		continue;
	}
	if(empty($srcData["paragraph"])){
		fwrite(STDERR,"paragraph is null {$uuid}".PHP_EOL);
		continue;
	}	
	if(empty($srcData["wid"])){
		#上线之前的旧数据错误 2842个 无需处理直接丢弃
		//fwrite(STDERR,"wid is null {$uuid}".PHP_EOL);
		$allSrcCount--;
		continue;
	}
	if(!is_numeric($srcData["wid"])){
		# 非数字系统无法处理，直接丢弃
		$allSrcCount--;
		continue;
	}
	if($srcData["wid"]>4000 || $srcData["wid"]<1){
		#过大过小的数字，直接丢弃
		$allSrcCount--;
		continue;
	}
	if(empty($srcData["data"])){
		$srcData["data"] = '';
	}
	if(empty($srcData["status"])){
		$srcData["status"] = 10;
	}
	if(empty($srcData["owner"])){
		fwrite(STDERR,"owner is null {$uuid}".PHP_EOL);
		continue;
	}
	if(empty($srcData["modify_time"])){
		fwrite(STDERR,"modify_time is null {$uuid}".PHP_EOL);
		continue;
	}
	if(empty($srcData["receive_time"])){
		fwrite(STDERR,"receive_time is null {$uuid}".PHP_EOL);
		continue;
	}
	if($srcData["modify_time"]>$srcData["receive_time"]){
		$created_at = (int)$srcData["receive_time"];
		$updated_at = (int)$srcData["modify_time"];
	}else{
		$created_at = (int)$srcData["modify_time"];
		$updated_at = (int)$srcData["receive_time"];
	}
	$book = ltrim($srcData["book"],'p');
	#查询目标表中是否有相同数据
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($uuid));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if(!$exist){
		if(strlen($srcData["owner"])>36){
			fwrite(STDERR, "owner too long ".$srcData["owner"].PHP_EOL);
			continue;
		}
		#没有相同的数据就插入
		$commitData[] = array(
			$snowflake->id(),
			$uuid,
			$srcData["block_id"],
			$book,
			$srcData["paragraph"],
			$srcData["wid"],
			$srcData["word"],
			$srcData["data"],
			$srcData["status"],
			$userId["userid"],
			$userId["id"],
			$created_at,
			$updated_at,
			$created_at/1000,
			$updated_at/1000
		);
		$count++;
		$allInsertCount++;
		if($count === 10000){
			$PDO_DEST->beginTransaction();
			$stmtDEST = $PDO_DEST->prepare($queryInsert);
			foreach ($commitData as $key => $value) {
				# code...
				$stmtDEST->execute($value);
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
	}
	// 提交更改
	$PDO_DEST->commit();
	echo "finished $count".PHP_EOL;
}

echo "insert done $allInsertCount in $allSrcCount ".PHP_EOL;

echo "all done in ".(time()-$start).'s'.PHP_EOL;






