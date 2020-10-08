<?php
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../path.php";

$_data = array();
if(isset($_POST["data"])){
	$_data = json_decode($_POST["data"],true);
}
else{
	if(isset($_GET["id"])){
		$id=$_GET["id"];
	}
	else{
		echo "error: no id";
		return;
	}
	if(isset($_GET["info"])){
		$info=$_GET["info"];
	}
	else{
		echo "error: no info";
		return;
	}
	$_data[] = array("id"=>$id,"data"=>$info);
}

if(isset($_POST["setting"])){
	$_setting = json_decode($_POST["setting"],true);
}
else{
	$_setting["lang"] = "";
	$_setting["channal"] = "";
}

$dns = "sqlite:"._FILE_DB_PALI_SENTENCE_;
$db_pali_sent = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$db_pali_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$dns = "sqlite:"._FILE_DB_SENTENCE_;
$db_trans_sent = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$db_trans_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$output = array();

foreach ($_data as $key => $value) {
	# code...
	$id = $value["id"];
	$arrInfo = explode("@",$value["data"]);
	if(isset($arrInfo[1])){
		$sentChannal = $arrInfo[1];
	}
	else{
		$sentChannal = "";
	}
	if(isset($arrInfo[0])){
		$arrSent = str_getcsv($arrInfo[0],"-");
		$bookId=$arrSent[0];
		$para=$arrSent[1];
		$begin=$arrSent[2];
		$end=$arrSent[3];
	}
	else{
		$bookId=0;
		$para=0;
		$begin=0;
		$end=0;
	}

	$query="SELECT html FROM 'pali_sent' WHERE book = ? AND paragraph = ? AND begin = ? AND end = ? ";
	$sth = $db_pali_sent->prepare($query);
	$sth->execute(array($bookId,$para,$begin,$end));
	$row = $sth->fetch(PDO::FETCH_NUM);
	if ($row) {
		$palitext= $row[0];
	} else {
		$palitext="";
	}

	//find out translation
	$tran="";
	try{
		if(!empty($_setting["channal"])){
			$queryChannal = " AND channal = ? ";
		}
		else{
			$queryChannal ="";
		}
		$query="SELECT * FROM sentence WHERE book= ? AND paragraph= ? AND begin= ? AND end= ?  AND strlen >0  $queryChannal order by modify_time DESC limit 0 ,1 ";
		$stmt = $db_trans_sent->prepare($query);
		if(empty($_setting["channal"])){
			$stmt->execute(array($bookId,$para,$begin,$end));
		}
		else{
			$stmt->execute(array($bookId,$para,$begin,$end,$_setting["channal"]));
		}
		$Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
		if($Fetch){
			$tran = $Fetch["text"];
		}
		$tran_count = 1;
	}
	catch (Exception $e) {
		$tran = $e->getMessage();
		//echo 'Caught exception: ',  $e->getMessage(), "\n";
	}
	
	$para_path=_get_para_path($bookId,$para);

	$output[]=array("id"=>$id,
							   "palitext"=>$palitext,
							   "tran"=>$tran,
							   "ref"=>$para_path,
							   "tran_count"=>$tran_count,
							   "book"=>$bookId,
							   "para"=>$para,
							   "begin"=>$begin,
							   "end"=>$end
							);

}

echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>