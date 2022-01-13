<?php
require_once __DIR__."/../../../public/app/config.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});

$src_db=_PG_DB_PALI_SENTENCE_;#源数据库
$src_table=_PG_TABLE_PALI_SENT_ORG_;#源表名

$dest_db=_PG_DB_PALI_SENTENCE_;#目标数据库
$dest_table=_PG_TABLE_PALI_SENT_;#目标表名

fwrite(STDOUT, "migarate pali_sent_org".PHP_EOL);
#打开源数据库
$PDO_SRC = new PDO($src_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT, "open src".PHP_EOL);

#打开目标数据库
$PDO_DEST = new PDO($dest_db,_DB_USERNAME_,_DB_PASSWORD_,array(PDO::ATTR_PERSISTENT=>true));
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
fwrite(STDOUT, "open dest".PHP_EOL);


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
fwrite(STDOUT, "begin Transaction".PHP_EOL);

$PDO_DEST->beginTransaction();

$query = "INSERT INTO ".$dest_table." (id, book , paragraph , word_begin , word_end , length , count , text , html,sim_sents ) VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? ,?)";
try{
	$stmtDEST = $PDO_DEST->prepare($query);
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}

#从源数据表中读取
$query = "SELECT *  FROM ".$src_table." order by id asc";
try{
	$stmtSrc = $PDO_SRC->prepare($query);
	$stmtSrc->execute();
}catch(PDOException $e){
	fwrite(STDERR,"error:".$e->getMessage());
	exit;
}

$data = array();
$currMergeCell = 1;
while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
	#插入目标表
	$currData = array(
					$srcData["id"],
					$srcData["book"],
					$srcData["paragraph"],
					$srcData["word_begin"],
					$srcData["word_end"],
					$srcData["length"],
					$srcData["count"],
					$srcData["text"],
					$srcData["html"],
					$srcData["sim_sents"]);
	if($srcData["merge"]==1 ){
		$stmtDEST->execute($currData);
	}else if($srcData["merge"]>1){
		$currMergeCell = $srcData["merge"];
		$data = $currData;
	}else{
		$data["word_end"] = $srcData["word_end"];
		$data["text"] .= " ".$srcData["text"];
		$data["html"] .= " ".$srcData["html"];
		$data["lenght"] += $srcData["lenght"];
		$data["count"] += $srcData["count"];
		$currMergeCell--;
		if($currMergeCell==1){
			try{					
				$stmtDEST->execute($currData);		
			}catch(PDOException $e){
				fwrite(STDERR,"error:".$e->getMessage().implode(',',$currData));
				exit;
			}
		}
	}
    		
	$count++;
	if($count%10000==0){
		fwrite(STDOUT, "finished $count".PHP_EOL);
	}
}

// 提交更改
$PDO_DEST->commit();
fwrite(STDOUT, "done".PHP_EOL);






