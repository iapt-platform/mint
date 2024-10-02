<?php
//header('Content-type: application/json; charset=utf8');

require_once "../config.php";
require_once "../sync/function.php";
require_once "../redis/function.php";
require_once "../usent/function.php";

$input = (object) [
    "database" =>  _FILE_DB_SENTENCE_,
    "table" =>  "sentence",
    "uuid" =>  "id",
    "sync_id" =>  ["book","paragraph","begin","end","channal"],
    "modify_time" =>  "modify_time",
    "receive_time" =>  "receive_time",
	"where"=>"and ( channal IS NOT NULL )",
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


$output=array();
$output["error"]=0;
$output["message"]="";

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
	case "sync_count":
		$result = do_sync($input);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;		
	case "sync":
		$result = do_sync($input);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;
	case "get":
		$result = do_sync($input);
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;
	case "insert":
		if (isset($_POST["data"])) {
			$data = $_POST["data"];
			$arrData = json_decode($data, true);
			$Sent=new Sent_DB(redis_connect());
			$result = $Sent->insert($arrData);
			if($result){
				$output["message"]="添加成功";
			}
			else{
				$output["error"]=1;
				$output["message"]="添加失败";	
			}
		} else {
			$output["error"]=1;
			$output["message"]=$Sent->getError();
		}
		echo json_encode($output, JSON_UNESCAPED_UNICODE);
	break;	
	case "update":
		if (isset($_POST["data"])) {
			$data = $_POST["data"];
			$arrData = json_decode($data, true);
			$Sent=new Sent_DB(redis_connect());
			$result = $Sent->update($arrData);
			if($result==false){
				$output["error"]=1;
				$output["message"]="修改失败";
			}
			else{
				$output["error"]=0;
				$output["message"]="修改成功";
			}
		} else {
			$output["error"]=1;
			$output["message"]=$Sent->getError();
		}
		echo json_encode($output, JSON_UNESCAPED_UNICODE);
	break;	
}

?>