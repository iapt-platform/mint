<?php

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/pdo.php';
require dirname(__FILE__) . '/logger.php';

logger('debug','content update start');

$PDO = new PdoHelper;

$PDO->connectDb();

$query = "UPDATE fts_texts SET content = content,
bold_single = bold_single,
bold_double = bold_double,
bold_multiple = bold_multiple
WHERE book = ?;";

for ($i=1; $i < 218; $i++) { 
    $ok = $PDO->execute($query,[$i]);
    if($ok){
        logger('debug','book: '.$i. ' updated.');
    }else{
        logger('error','error book: '.$i. ' update fail.'.$PDO->errorInfo());
    }
}
logger('debug','all done');
