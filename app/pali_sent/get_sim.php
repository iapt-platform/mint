<?php
/*
get user sentence from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

//获取相似句子列表

if(isset($_POST["sent_id"])){
	$dns = "sqlite:"._FILE_DB_PALI_SENTENCE_SIM_;
	$dbh_sim = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
	$dbh_sim->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING); 
	$query="SELECT sent2 FROM sent_sim WHERE  sent1 = ? limit 0 , 10";
	$stmt = $dbh_sim->prepare($query);
	$stmt->execute(array($_POST["sent_id"]));
	$simList = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
else{
	$sim = $_POST["sim"];
	$simList = json_decode($sim);

}
$output = array();

$dns = "sqlite:"._FILE_DB_PALI_SENTENCE_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$query="SELECT * FROM pali_sent WHERE  id = ? ";
$stmt = $dbh->prepare($query);
$count = 0;
foreach ($simList as  $value) {
    # code...
    $stmt->execute(array($value["sent2"]));
	$Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
	if($Fetch){
		$sent = $Fetch;
		$sent["path"]=_get_para_path($Fetch["book"],$Fetch["paragraph"]);
		$output[] = $sent;  
	}

}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>