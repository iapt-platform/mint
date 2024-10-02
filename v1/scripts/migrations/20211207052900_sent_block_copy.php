<?php
/*
迁移 sentence库
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

# 更新索引表
$src_db = _SQLITE_DB_SENTENCE_;#源数据库
$src_table = _SQLITE_TABLE_SENTENCE_BLOCK_;#源表名
$dest_db = _PG_DB_SENTENCE_;#目标数据库
$dest_table = _PG_TABLE_SENTENCE_BLOCK_;#目标表名

fwrite(STDOUT,"migarate sent_block ".PHP_EOL);


#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open src table".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT,"open dest table".PHP_EOL);

$queryInsert = "INSERT INTO ".$dest_table." (
                                    id,
                                    uid, 
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
									VALUES ( ? , ? , ? , ? , ? ,? ,? ,? ,? ,? ,? ,?,to_timestamp(?),to_timestamp(?))";


$commitData = [];
$allInsertCount = 0;
$allSrcCount = 0;
$count = 0;

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table;
$stmtSrc = $PDO_SRC->prepare($query);
$stmtSrc->execute();
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	$allSrcCount++;
	#插入目标表

	if(substr($srcData["book"],0,1)==="p"){
		$srcData["book"] = (int)substr($srcData["book"],1);
	}
	
	if(strlen($srcData["id"])>10 && strlen($srcData["owner"])>30){
		$uuid = $srcData["id"];
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
        if(empty($srcData["lang"]) ){
            $srcData["lang"]="zh-hans";
        }
        if(empty($srcData["status"]) ){
            $srcData["status"]=10;
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

        if(empty($srcData["modify_time"]) || $srcData["modify_time"]<1532590551000){
            $srcData["modify_time"]=1532590551000;
        }
			$commitData[] = array(
                    $snowflake->id(),
					$uuid,
					$srcData["parent_id"],
					$srcData["book"],
					$srcData["paragraph"],
					$srcData["owner"],
					$srcData["lang"],
					$srcData["author"],
					$srcData["editor"],
					$srcData["status"],
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
                try{
                    $stmtDEST->execute($value);	
                }catch(PDOException $e){
                    fwrite(STDERR,$e->getMessage().PHP_EOL);
                    fwrite(STDERR,implode(',',$value).PHP_EOL);
                    continue;
                }
			}
			// 提交更改
			$PDO_DEST->commit();
			$commitData = [];
			fwrite(STDOUT,"finished $count".PHP_EOL) ;
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
	}
	// 提交更改
	$PDO_DEST->commit();
	$commitData = [];
	echo "finished $count".PHP_EOL;
}

echo "insert done $allInsertCount in $allSrcCount ".PHP_EOL;







