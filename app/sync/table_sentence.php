<?php
//header('Content-type: application/json; charset=utf8');

require_once "../path.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_SENTENCE_,
    "table" =>  "sentence",
    "uuid" =>  "id",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "modify_time",
    "insert" => [
        'id',
		'block_id',
		'book',
		'paragraph',
		'begin',
		'end',
		'tag',
		'author',
		'editor',
		'text',
		'language',
		'ver',
		'status',
		'channal',
		'parent',
		'strlen',
		'create_time',
		'modify_time',
		'receive_time'
    ],
    "update" =>  [
		'block_id',
		'tag',
		'author',
		'editor',
		'text',
		'language',
		'ver',
		'status',
		'channal',
		'parent',
		'strlen',
		'modify_time',
		'receive_time'
    ]    
];

do_sync($input);

?>