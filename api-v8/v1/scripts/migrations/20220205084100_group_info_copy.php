<?php
/*
迁移  article 库
从旧数据表中提取数据插入到新的表
插入时用uuid判断是否曾经插入
曾经插入就不插入了
*/
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

$fpError = fopen(__DIR__.'/log/'.basename($_SERVER['PHP_SELF'],'.php').".err.data.csv",'w');

#user info
$user_db=_FILE_DB_USERINFO_;#user数据库
$user_table=_TABLE_USER_INFO_;#user表名

# 
$src_db = _SQLITE_DB_GROUP_;#源数据库
$src_table = _SQLITE_TABLE_GROUP_INFO_;#源表名

$dest_db = _PG_DB_GROUP_;#目标数据库
$dest_table = _PG_TABLE_GROUP_INFO_;#目标表名

fwrite(STDOUT,"migarate group info".PHP_EOL);
#打开user数据库
$PDO_USER = new PDO($user_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_USER->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open user table".PHP_EOL);

#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open dest table".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." 
								(
                                    id,
									uid,
									name,
									description,
									status,
									owner,
									create_time,
									modify_time,
									updated_at,
									created_at) 
									VALUES (? , ? , ? , ?, ? , ? , ? , ? , ? , ? )";
$stmtDEST = $PDO_DEST->prepare($queryInsert);

$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从user数据表中读取
$query = "SELECT id  FROM ".$user_table." WHERE userid = ? ";
$stmtUser = $PDO_USER->prepare($query);

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table;
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;

    if($srcData["owner"]=='visuddhinanda'){
		$srcData["owner"] = 'ba5463f3-72d1-4410-858e-eadd10884713';
	}
    if($srcData["owner"]=='test7'){
		$srcData["owner"] = '6bd2f4d7-d970-419c-8ee5-f4bac42f4bc1';
	}
    if($srcData["owner"]=='Dhammadassi'){
		$srcData["owner"] = 'd8538ebd-d369-4777-b99a-3ccb1aff8bfc';
	}
    if($srcData["owner"]=='pannava'){
		$srcData["owner"] = '4db550c4-bc1b-43f2-a518-2740cb478f37';
	}
    if($srcData["owner"]=='NST'){
		$srcData["owner"] = '5c23e629-56a3-48e9-97c7-2af73b59c3b9';
	}
    if($srcData["owner"]=='viranyani'){
		$srcData["owner"] = 'C1AB2ABF-EAA8-4EEF-B4D9-3854321852B4';
	}
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
	$stmtUser->execute(array($srcData["owner"]));
	$userId = $stmtUser->fetch(PDO::FETCH_ASSOC);
	if(!$userId){
		fwrite(STDERR,time()."error,no user id {$srcData["owner"]}".PHP_EOL);
		continue;
	}
	if(strlen($srcData["owner"])>36){
		fwrite(STDERR,time().",error,user id too long {$srcData["owner"]}".PHP_EOL);
		continue;	
	}
    if(empty($srcData["name"])){
		fwrite(STDERR,time().",error,name is empty {$srcData["id"]}".PHP_EOL);
		continue;
	}

    $srcData["status"] = 30;

    if(empty($srcData["modify_time"])){
        $srcData["modify_time"] = $srcData["create_time"];
    }

    if($srcData["create_time"] < 15987088320){
        $srcData["create_time"] *= 1000;
    }
    if($srcData["modify_time"] < 15987088320){
        $srcData["modify_time"] *= 1000;
    }
	//查询是否已经插入
	$queryExsit = "SELECT id  FROM ".$dest_table." WHERE uid = ? ";
	$getExist = $PDO_DEST->prepare($queryExsit);
	$getExist->execute(array($srcData["id"]));
	$exist = $getExist->fetch(PDO::FETCH_ASSOC);
	if($exist){
		continue;
	}
	#插入目标表
	$created_at = date("Y-m-d H:i:s.",$srcData["create_time"]/1000).($srcData["create_time"]%1000)." UTC";
	$updated_at = date("Y-m-d H:i:s.",$srcData["modify_time"]/1000).($srcData["modify_time"]%1000)." UTC";
	$commitData = array(
            $snowflake->id(),
			$srcData["id"],
			$srcData["name"],
			$srcData["description"],
			$srcData["status"],
			$srcData["owner"],
			$srcData["create_time"],
			$srcData["modify_time"],
			$created_at,
			$updated_at
		);
	$stmtDEST->execute($commitData);

	$count++;	
	$allInsertCount++;


	if($count ==10000){
		#10000行输出log 一次
		echo "finished $count".PHP_EOL;
		$count=0;
	}	
}

fwrite(STDOUT,"insert done $allInsertCount in $allSrcCount ".PHP_EOL) ;
fwrite(STDOUT, "all done in ".(time()-$start)."s".PHP_EOL);

fclose($fpError);




