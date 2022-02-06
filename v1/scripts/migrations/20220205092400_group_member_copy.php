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
$src_table = _SQLITE_TABLE_GROUP_MEMBER_;#源表名

$dest_db = _PG_DB_GROUP_;#目标数据库
$dest_table = _PG_TABLE_GROUP_MEMBER_;#目标表名

fwrite(STDOUT,"migarate group member ".PHP_EOL);
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
									user_id,
									group_id,
									group_name,
									power,
									level,
									status,
									created_at,
									updated_at) 
									VALUES (? , ? , ? , ?, ? , ? ,?  , now() , now() )";
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

    $queryExist = "SELECT *  FROM ".$dest_table." where user_id=? and group_id=? ";
    $stmtExist = $PDO_DEST->prepare($queryExist);
    $stmtExist->execute([$srcData["user_id"],$srcData["group_id"]]);
    $isExist = $stmtExist->fetch(PDO::FETCH_ASSOC);
	if($isExist){
        echo "record is existed id=".$srcData['id'].PHP_EOL;
        continue;
    }

{
    if($srcData["user_id"]=='visuddhinanda'){
		$srcData["user_id"] = 'ba5463f3-72d1-4410-858e-eadd10884713';
	}
    if($srcData["user_id"]=='test7'){
		$srcData["user_id"] = '6bd2f4d7-d970-419c-8ee5-f4bac42f4bc1';
	}
    if($srcData["user_id"]=='Dhammadassi'){
		$srcData["user_id"] = 'd8538ebd-d369-4777-b99a-3ccb1aff8bfc';
	}
    if($srcData["user_id"]=='pannava'){
		$srcData["user_id"] = '4db550c4-bc1b-43f2-a518-2740cb478f37';
	}
    if($srcData["user_id"]=='NST'){
		$srcData["user_id"] = '5c23e629-56a3-48e9-97c7-2af73b59c3b9';
	}
    if($srcData["user_id"]=='viranyani'){
		$srcData["user_id"] = 'C1AB2ABF-EAA8-4EEF-B4D9-3854321852B4';
	}
    if($srcData["user_id"]=='test6'){
		$srcData["user_id"] = 'f81c7140-64b4-4025-b58c-45a3b386324a';
	}
	if($srcData["user_id"]=='test28'){
		$srcData["user_id"] = 'df0ad9bc-c0cd-4cd9-af05-e43d23ed57f0';
	}
	if($srcData["user_id"]=='290fd808-2f46-4b8c-b300-0367badd67ed'){
		$srcData["user_id"] = 'f81c7140-64b4-4025-b58c-45a3b386324a';
	}
	if($srcData["user_id"]=='BA837178-9ABD-4DD4-96A0-D2C21B756DC4'){
		$srcData["user_id"] = 'ba5463f3-72d1-4410-858e-eadd10884713';
	}
	$stmtUser->execute(array($srcData["user_id"]));
	$userId = $stmtUser->fetch(PDO::FETCH_ASSOC);
	if(!$userId){
		fwrite(STDERR,time()."error,no user id {$srcData["user_id"]}".PHP_EOL);
		continue;
	}
}


	if(strlen($srcData["user_id"])>36){
		fwrite(STDERR,time().",error,user id too long {$srcData["user_id"]}".PHP_EOL);
		continue;	
	}

	#插入目标表
	$commitData = array(
            $snowflake->id(),
			$srcData["user_id"],
			$srcData["group_id"],
			$srcData["group_name"],
			$srcData["power"],
			$srcData["level"],
			$srcData["status"]
		);
    try{
        $stmtDEST->execute($commitData);
    }catch (Exception $e) {
        echo "Failed: " . $e->getMessage();
    }
	

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




