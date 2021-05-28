<?php

require_once "../path.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_USER_WBW_,
    "table" =>  "wbw",
    "uuid" =>  "id",
    "sync_id" =>  ["block_id","wid"],
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
	"where"=>" and owner = 'C1AB2ABF-EAA8-4EEF-B4D9-3854321852B4' ",
    "insert" => [
        'id',
		'block_id',
		'book',
		'paragraph',
		'wid',
		'word',
		'data',
		'status',
		'owner',
		'receive_time',
		'modify_time'
    ],
    "update" =>  [
        'id',
		'book',
		'paragraph',
		'word',
		'data',
		'status',
		'owner',
		'receive_time',
		'modify_time'
    ]    
];

$result = do_sync($input);
echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>