<?php
require_once "../public/function.php";
require_once "../path.php";
require_once '../channal/function.php';
require_once '../ucenter/function.php';



$_data = array();
$output = array();
if(isset($_POST["data"])){
	$_data = json_decode($_POST["data"],true);
}
else{
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}

$_userinfo = new UserInfo();
$_channal = new Channal();

$dns = "sqlite:"._FILE_DB_SENTENCE_;
$db_trans_sent = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$db_trans_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  


$channal = array();

foreach ($_data as $key => $value) {
	# code...
	$id = $value["id"];
	$arrInfo = str_getcsv($value["data"],"@");
	$arrSent = str_getcsv($arrInfo[0],"-");
	$bookId=$arrSent[0];
	$para=$arrSent[1];
	$begin=$arrSent[2];
	$end=$arrSent[3];

	//find out translation
	$tran="";
	try{
		$query="SELECT channal FROM sentence WHERE book= ? AND paragraph= ? AND begin= ? AND end= ?  AND strlen >0  group by channal  limit 0 ,20 ";
		$stmt = $db_trans_sent->prepare($query);
		$stmt->execute(array($bookId,$para,$begin,$end));
		$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
		foreach ($Fetch as $key => $value) {
            # code...
            if(isset($value["channal"])){
                $channal[$value["channal"]] ++;
			}
			else{
				$channal[$value["channal"]] = 1;
			}
            
        }
	}
	catch (Exception $e) {
		$tran = $e->getMessage();
		//echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	


}

foreach ($channal as $key => $value) {
    # code...
    $channalInfo = $_channal->getChannal($key);
    $name = $_userinfo->getName($channalInfo["owner"]);
    $channalInfo["username"] = $name["username"];
	$channalInfo["nickname"] = $name["nickname"];
	$channalInfo["count"] = $value;
	$channalInfo["all"] = count($_data);
    $output[]= $channalInfo;
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>