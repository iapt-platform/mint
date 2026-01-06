<?php

require dirname(__FILE__) . '/vendor/autoload.php';
require dirname(__FILE__) . '/config.php';
require dirname(__FILE__) . '/pdo.php';
require dirname(__FILE__) . '/console.php';

console('debug','dict update start');

$PDO = new PdoHelper;

$PDO->connectDb();

$query = "ALTER TEXT SEARCH DICTIONARY pali_stem (
    SYNONYMS = pali
);";

$ok = $PDO->execute($query);
if($ok){
    console('debug','dictionary updated');
}else{
    console('error','dictionary update fail.'.$PDO->errorInfo());
}

console('debug','all done');
