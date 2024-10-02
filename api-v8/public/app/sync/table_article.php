<?php
//header('Content-type: application/json; charset=utf8');

require_once "../config.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_USER_ARTICLE_,
    "table" =>  "article",
    "uuid" =>  "id",
    "sync_id" =>  ["id"],
	"where"=>"",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
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

$result = do_sync($input);
echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>