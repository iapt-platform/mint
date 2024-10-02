<?php
//header('Content-type: application/json; charset=utf8');

require_once "../config.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_CHANNAL_,
    "table" =>  "channal",
    "uuid" =>  "id",
    "sync_id" =>  ["id"],
	"where"=>"",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
    "insert" => [
        'id',
		'owner',
		'name',
		'summary',
		'status',
		'lang',
		'create_time',
		'modify_time',
		'receive_time'
    ],
    "update" =>  [
		'owner',
		'name',
		'summary',
		'status',
		'lang',
		'modify_time'
    ]
];

$result = do_sync($input);
echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>