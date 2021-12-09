<?php
require_once '../config.php';
require_once '../public/_pdo.php';
require_once '../redis/function.php';
require_once '../dict/function.php';

if (isset($_GET["language"])) {
    $currLanguage = $_GET["language"];
} else {
    if (isset($_COOKIE["language"])) {
        $currLanguage = $_COOKIE["language"];
    } else {
        $currLanguage = "en";
    }
}
$currLanguage = explode("-", $currLanguage)[0];

$output=array();
if(isset($_GET["word"])){
	$arrWords = explode("+",$_GET["word"]);
}
else{
	echo json_encode($output, JSON_UNESCAPED_UNICODE);
	exit;
}
$redis = redis_connect();

if($redis!==false){
	foreach ($arrWords as $key => $word) {
		# code...
		$output[]=array("word"=>$word,"mean"=>getRefFirstMeaning($word,$currLanguage,$redis));
	}
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>