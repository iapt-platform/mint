<?php
//header('Content-type: application/json; charset=utf8');

require_once "../config.php";
require_once "../public/function.php";
require_once "../sync/function.php";
require_once "../collect/function.php";
require_once "../redis/function.php";

$input = (object) [
    "database" =>  _FILE_DB_USER_ARTICLE_,
    "table" =>  "collect",
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
			foreach ($arrData as $key => $value) {
				# 设置数据同步时间
				$arrData[$key]["receive_time"]=mTime();
			}
			$collection=new CollectInfo(redis_connect());
			$result = $collection->insert($arrData);
			if($result==false){
				$output["error"]=1;
				$output["message"]="没有提交数据";				
			}
		} else {
			$output["error"]=1;
			$output["message"]=$collection->getError();
		}
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;	
	case "update":
		if (isset($_POST["data"])) {
			$data = $_POST["data"];
			$arrData = json_decode($data, true);
			foreach ($arrData as $key => $value) {
				# 设置数据同步时间
				$arrData[$key]["receive_time"]=mTime();
			}
			$collection=new CollectInfo(redis_connect());
			$result = $collection->update($arrData);
			if($result==false){
				$output["error"]=1;
				$output["message"]="没有提交数据";				
			}
		} else {
			$output["error"]=1;
			$output["message"]=$collection->getError();
		}
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;
	default:
		$output["error"]=1;
		$output["message"]="错误的操作码";	
		echo json_encode($result, JSON_UNESCAPED_UNICODE);
	break;
}


?>