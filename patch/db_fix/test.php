<?php

require_once "tables.php";
require_once "config.php";
require_once "function.php";

if(php_sapi_name() !== "cli") {
    echo 'no cli';
    return;
}

$src_db = 'db_b';
$dest_db = 'db_c';

#打开源数据库
$PDO_SRC = openDb($config[$src_db]);
$PDO_SRC->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

#打开目标数据库
$PDO_DEST = openDb($config[$dest_db]);
$PDO_DEST->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

foreach ($tables as $tableName => $table) {
    fwrite(STDOUT,$tableName.PHP_EOL);
    $query = "SELECT count(*)  FROM {$tableName}";
    $stmtSrc = $PDO_SRC->prepare($query);
    $stmtSrc->execute();
    $srcData = $stmtSrc->fetch(PDO::FETCH_ASSOC);
    fwrite(STDOUT,'table 1 count='.$srcData['count'].PHP_EOL);

    $stmtDest = $PDO_DEST->prepare($query);
    $stmtDest->execute();
    $destData = $stmtDest->fetch(PDO::FETCH_ASSOC);
    fwrite(STDOUT,'table 2 count='.$destData['count'].PHP_EOL);

    fwrite(STDOUT,'field='.count($table['fields']).PHP_EOL);
    $fields = '"' . implode('","',$table['fields']) . '"' ;
    $query = "SELECT {$fields}  FROM {$tableName} limit 1 ";
    $stmtSrc = $PDO_SRC->prepare($query);
    $stmtSrc->execute();

    $keys = array();
    if(is_array($table['key'])){
        $keys = $table['key'];
    }else{
        $keys[] = $table['key'];
    }
    if(!empty($table['time1'])){
        $keys[] = $table['time1'];
    }
    if(!empty($table['time1'])){
        $keys[] = $table['time2'];
    }
    foreach ($keys as $key) {
        if(!in_array($key,$table['fields'])){
            fwrite(STDERR,$tableName.' no field '.$key.PHP_EOL);
        }
    }

    fwrite(STDOUT,PHP_EOL);
}