<?php
//header('Content-type: application/json; charset=utf8');

require_once "../config.php";
require_once "../sync/function.php";

$input = (object) [
    "database" =>  _FILE_DB_SENTENCE_,
    "table" =>  "sent_block",
    "uuid" =>  "id",
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
    "insert" => [
        "id",
        "block_id",
        "book",
        "paragraph",
        "begin",
        "end",
        "tag",
        "author",
        "editor",
        "text",
        "language",
        "ver",
        "status",
        "modify_time",
        "receive_time"
    ],
    "update" =>  [
        "tag",
        "author",
        "editor",
        "text",
        "language",
        "ver",
        "status",
        "receive_time"
    ]
];

do_sync($input);

?>