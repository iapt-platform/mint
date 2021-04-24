<?php
//header('Content-type: application/json; charset=utf8');

require_once "../path.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_USER_ARTICLE_,
    "table" =>  "article",
    "uuid" =>  "id",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "modify_time",
    "insert" => [
        'id',
		'title',
		'subtitle',
		'summary',
		'content',
		'tag',
		'owner',
		'setting',
		'status',
		'create_time',
		'modify_time',
		'receive_time'
    ],
    "update" =>  [
		'title',
		'subtitle',
		'summary',
		'content',
		'tag',
		'owner',
		'setting',
		'status',
		'modify_time',
		'receive_time'
    ]    
];

do_sync($input);

?>