<?php

function dbInfo($db){
    return $db['driver'].':'.
    "host={$db['host']};".
    "port={$db['port']};".
    "dbname={$db['database']};".
    "user={$db['username']};".
    "password={$db['password']};";
}

function openDb($dbInfo){
    return new PDO(dbInfo($dbInfo),
                $dbInfo['username'],
                $dbInfo['password'],
                array(PDO::ATTR_PERSISTENT=>true));
}