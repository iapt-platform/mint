<?php

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/pdo.php';
require dirname(__FILE__) . '/logger.php';

logger('debug','dict update start');

$PDO = new PdoHelper;

$PDO->connectDb();

$query = "ALTER TEXT SEARCH DICTIONARY pali_stem (
    SYNONYMS = pali
);";

$ok = $PDO->execute($query);
if($ok){
    logger('debug','dictionary updated');
}else{
    logger('error','dictionary update fail.'.$PDO->errorInfo());
}

logger('debug','all done');
