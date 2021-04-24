<?php
//header('Content-type: application/json; charset=utf8');

require_once "../path.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_USER_ARTICLE_,
    "table" =>  "article_list",
    "uuid" =>  "id",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "modify_time",
    "insert" => [
        'id',
		'collect_id',
		'collect_title',
		'article_id',
		'level',
		'title',
		'create_time',
		'modify_time'
    ],
    "update" =>  [
		'collect_id',
		'collect_title',
		'article_id',
		'level',
		'title',
		'create_time',
		'modify_time'
    ]    
];

do_sync($input);

?>