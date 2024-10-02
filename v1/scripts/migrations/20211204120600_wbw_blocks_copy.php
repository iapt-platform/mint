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

$fpError = fopen(__DIR__.'/log/'.basename($_SERVER['PHP_SELF'],'.php').".err.data.csv",'w');

# 更新索引表
#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名

$src_db=_SQLITE_DB_USER_WBW_;#源数据库
$src_table=_SQLITE_TABLE_USER_WBW_BLOCK_;#源表名

$dest_db=_PG_DB_USER_WBW_;#目标数据库
$dest_table=_PG_TABLE_USER_WBW_BLOCK_;#目标表名

# 雪花id
$snowflake = new SnowFlakeId();

fwrite(STDOUT, "migarate wbw_block".PHP_EOL);

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

fwrite(STDOUT, "begin Transaction".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." 
								   (
									id,
									uid, 
									parent_id , 
									channel_uid, 
									parent_channel_uid,
									creator_uid,
									editor_id,
									book_id,
									paragraph,
									style,
									lang,
									status,
									create_time,
									modify_time,
									created_at,
									updated_at
									) 
									VALUES ( ? , ? , ? , ? , ? , ? ,? , ? , ? , ? , ? , ? , ? , ? , to_timestamp(?),to_timestamp(?))";


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
	if(empty($srcData["book"])){
		fwrite(STDERR,"book is null {$uuid}".PHP_EOL);
		continue;
	}
	if(substr($srcData["book"],0,1)==="p"){
		$srcData["book"] = substr($srcData["book"],1);
	}
	if(empty($srcData["paragraph"])){
		fwrite(STDERR,"paragraph is null {$uuid}".PHP_EOL);
		continue;
	}	

	if(empty($srcData["parent_id"])){
		$srcData["parent_id"] = NULL;
	}
	if(empty($srcData["channal"])){
		$srcData["channal"] = NULL;
	}
	if(empty($srcData["status"])){
		$srcData["status"] = 10;
	}

	if(empty($srcData["create_time"]) || $srcData["create_time"]<1532590551000){
		$srcData["create_time"]=1532590551000;
	}
	if(empty($srcData["modify_time"]) || $srcData["modify_time"]<1532590551000){
		$srcData["modify_time"]=1532590551000;
	}

	$uuid = $srcData["id"];
	#查询目标表中是否有相同数据
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($uuid));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if(!$exist){
		#没有相同数据
		if(strlen($srcData["parent_id"])>36){
			fwrite(STDERR, "parent_id too long ".$srcData["parent_id"].PHP_EOL);
			continue;
		}
		if(strlen($srcData["channal"])>36){
			fwrite(STDERR, "channal too long ".$srcData["channal"].PHP_EOL);
			continue;
		}
		if(strlen($srcData["parent_channel"])>36){
			fwrite(STDERR, "parent_channel too long ".$srcData["parent_channel"].PHP_EOL);
			continue;
		}
		if(strlen($srcData["owner"])>36){
			fwrite(STDERR, "owner too long ".$srcData["owner"].PHP_EOL);
			continue;
		}
        if(empty($srcData["book"]) || !is_numeric($srcData["book"])){
            fwrite(STDERR,"book is error id=".$uuid.PHP_EOL);
            fputcsv($fpError,$srcData);
            continue;
        }
        if(empty($srcData["paragraph"]) || !is_numeric($srcData["paragraph"])){
            fwrite(STDERR,"paragraph is error id=".$uuid.PHP_EOL);
            fputcsv($fpError,$srcData);
            continue;
        }
		$commitData[] = array(
				$snowflake->id(),
				$uuid,
				$srcData["parent_id"],
				$srcData["channal"],
				$srcData["parent_channel"],
				$userId["userid"],
				$userId["id"],
				$srcData["book"],
				$srcData["paragraph"],
				$srcData["style"],
				$srcData["lang"],
				$srcData["status"],
				$srcData["create_time"],
				$srcData["modify_time"],
				$srcData["create_time"]/1000,
				$srcData["modify_time"]/1000
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
		}
		// 提交更改
		$PDO_DEST->commit();
		$commitData = [];
		fwrite(STDOUT, "finished $count".PHP_EOL);
		$count=0;
	}	
	
}
if($count>0){
	#最后的剩余的数据插入
	$PDO_DEST->beginTransaction();
	$stmtDEST = $PDO_DEST->prepare($queryInsert);
	foreach ($commitData as $key => $value) {
		$stmtDEST->execute($value);
	}
	// 提交更改
	$PDO_DEST->commit();
	$commitData = [];
	fwrite(STDOUT, "finished $count".PHP_EOL);
}

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL);

fwrite(STDOUT,"all done".PHP_EOL);

fclose($fpError);





