<?php

require_once "../config.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_TERM_,
    "table" =>  "term",
    "uuid" =>  "guid",
    "sync_id" =>  ["pali","tag","owner"],
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
	"where"=>" and ( (channal IS NULL) or channal = '' )",
    "insert" => [
        'guid',
		'word',
		'word_en',
		'meaning',
		'other_meaning',
		'note',
		'tag',
		'create_time',
		'owner',
		'hit',
		'language',
		'receive_time',
		'modify_time'
    ],
    "update" =>  [
        "word",
        "word_en",
        "meaning",
        "other_meaning",
        "note",
        "tag",
        "owner",
        "hit",
        "language",
        "create_time",
		"modify_time",
        "receive_time" 
    ]    
];

$result = do_sync($input);
echo json_encode($result, JSON_UNESCAPED_UNICODE);

?>