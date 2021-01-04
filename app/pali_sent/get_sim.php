<?php
/*
get user sentence from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$sim = $_POST["sim"];

$simList = json_decode($sim);
$output = array();

$dns = "sqlite:"._FILE_DB_PALI_SENTENCE_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$query="SELECT * FROM pali_sent WHERE  id = ?   ";
$stmt = $dbh->prepare($query);
$count = 0;
foreach ($simList as  $value) {
    # code...
    $stmt->execute(array($value));
	$Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
	if($Fetch){
		$sent = $Fetch;
		$sent["path"]=_get_para_path($Fetch["book"],$Fetch["paragraph"]);
		$output[] = $sent;  
	}
	$count++;
	if($count>15){
		break;
	}
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>