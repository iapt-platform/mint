<?php
//header('Content-type: application/json; charset=utf8');

require_once "../config.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_FILEINDEX_,
    "table" =>  "fileindex",
    "uuid" =>  "id",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
    "insert" => [
        "id", 
        "parent_id", 
        "user_id", 
        "book", 
        "paragraph", 
        "file_name", 
        "title", 
        "tag", 
        "status", 
        "create_time",
        "modify_time",
        "accese_time",
        "file_size",
        "share",
        "doc_info",
        "doc_block"
    ],
    "update" =>  [
        "title",
        "tag",
        "modify_time",
        "accese_time",
        "share",
        "doc_info",
        "doc_block",
        "receive_time" 
    ]    
];

do_sync($input);

?>