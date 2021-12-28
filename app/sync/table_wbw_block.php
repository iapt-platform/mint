<?php
//header('Content-type: application/json; charset=utf8');

require_once "../config.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_USER_WBW_,
    "table" =>  _TABLE_USER_WBW_BLOCK_,
    "uuid" =>  "id",
    "sync_id" =>  ["id"],
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
	"where"=>" and ( (channal IS NOT NULL) or channal <> '')  ",
    "insert" => [
        'id',
		'parent_id',
		'channal',
		'owner',
		'book',
		'paragraph',
		'style',
		'lang',
		'status',
		'receive_time',
		'modify_time'
    ],
    "update" =>  [
		'parent_id',
		'channal',
		'owner',
		'book',
		'paragraph',
		'style',
		'lang',
		'status',
		'modify_time'
    ]
];

$result = do_sync($input);
echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>