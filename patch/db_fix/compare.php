<?php

require_once "tables.php";
require_once "config.php";
require_once "function.php";

if(php_sapi_name() !== "cli") {
    echo 'no cli';
    return;
}

if(count($argv)<3){
    echo 'expect 2 db '.(count($argv)-1).' gave';
    return;
}
$src_db = $argv[1];
$dest_db = $argv[2];

#打开源数据库
$PDO_SRC = openDb($config[$src_db]);
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#打开目标数据库
$PDO_DEST = openDb($config[$dest_db]);
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

if(isset($argv[3])){
    $on = false;
}else{
    $on = true;
}
foreach ($tables as $tableName => $table) {
    if(isset($argv[3])){
        if($tableName !== $argv[3]){
            continue;
            //$on = true;
        }
    }
    /*
    if(!$on){
        continue;
    }
    */
    //A -> B 差异
    fwrite(STDOUT,$tableName.PHP_EOL);
    if($table['user'] === false){
        fwrite(STDOUT,'not user data ignore'.PHP_EOL);
        continue;
    }
    $keys = array();
    if(is_array($table['key'])){
        $keys = $table['key'];
    }else{
        $keys[] = $table['key'];
    }
    $select = $keys;
    if(!empty($table['time1'])){
        $select[] = $table['time1'];
    }
    if(!empty($table['time2'])){
        $select[] = $table['time2'];
    }
    $query = "SELECT * FROM {$tableName} ";
    $stmtSrc = $PDO_SRC->prepare($query);
    $stmtSrc->execute();
    $count = 0;

    $where = [];
    foreach ($keys as $value) {
        $where[] = "{$value} = ? ";
    }
    $query = "SELECT ".implode(',',$select)." FROM {$tableName} where ". implode(' and ',$where);
    $stmtDest = $PDO_DEST->prepare($query);
    $countUpdate = 0;
    $countDown = 0;
    $countNew = 0;
    while($srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC)){
        $count++;
        if($count % 1000 === 0){
            fwrite(STDOUT,$count.PHP_EOL);
        }
        $param  = [];
        foreach ($keys as $value) {
            $param[] = $srcData[$value];
        }
        $stmtDest->execute($param);
        $row = $stmtDest->fetch(PDO::FETCH_ASSOC);
        $realKey = implode(' and ',$where).' = '.implode(',',$param);
        if($row) {
            //时间
            $t1a=0;
            $t1b=0;
            $t2a=0;
            $t2b=0;
            $ta=0;
            $tb=0;
            if(!empty($table['time1'])){
                $t1a=$srcData[$table['time1']]/1000;
                $t1b=$row[$table['time1']]/1000;
            }
            if(!empty($table['time2'])){
                $t2a=strtotime($srcData[$table['time2']]);
                $t2b=strtotime($row[$table['time2']]);
            }
            //两个时间戳，取较新的。
            $ta = $t1a > $t2a? $t1a:$t2a;
            $tb = $t1b > $t2b? $t1b:$t2b;

            if($ta > $tb ){
                fwrite(STDOUT,$tableName.' update '.$realKey.PHP_EOL);
                fwrite(STDOUT,' update '.$row[$table['time2']].' -> '.$srcData[$table['time2']].PHP_EOL);
                $countUpdate++;
            }else if($ta < $tb){
                fwrite(STDOUT,$tableName.' down date '.$realKey.PHP_EOL);
                $countDown++;
            }
        }else{
            //缺失
            if(isset($srcData['uid'])){
                $title = $srcData['uid'];
            }else{
                $title = '';
            }
            fwrite(STDOUT,$tableName." new title={$title} ".$realKey.PHP_EOL);
            $countNew++;
        }
    }
    fwrite(STDOUT,"table={$tableName}".PHP_EOL);
    fwrite(STDOUT,"Update={$countUpdate}".PHP_EOL);
    fwrite(STDOUT,"Down={$countDown}".PHP_EOL);
    fwrite(STDOUT,"New={$countNew}".PHP_EOL);
    fwrite(STDOUT,PHP_EOL);
}
