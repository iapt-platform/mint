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



if (isset($_GET["op"])) {
	$op = $_GET["op"];
} else if (isset($_POST["op"])) {
	$op = $_POST["op"];
} else {
	$output["error"]=1;
	$output["message"]="无操作码";
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}

switch ($op) {
	case "sync":
		$result = do_sync($input);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;
	case "get":
		$result = do_sync($input);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;
	case "insert":
		
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;	
}


?>