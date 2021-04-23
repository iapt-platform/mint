<?php
//header('Content-type: application/json; charset=utf8');

require_once "../path.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_USER_ARTICLE_,
    "table" =>  "collect",
    "uuid" =>  "id",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "modify_time",
    "insert" => [
        'id',
		'title',
		'subtitle',
		'summary',
		'article_list',
		'status',
		'owner',
		'lang',
		'create_time',
		'modify_time',
		'tag'
    ],
    "update" =>  [
		'title',
		'subtitle',
		'summary',
		'article_list',
		'status',
		'owner',
		'lang',
		'create_time',
		'modify_time',
		'tag'
    ]    
];

do_sync($input);

?>