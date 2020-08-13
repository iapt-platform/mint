<?php
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../path.php";


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
$arrInfo = str_getcsv($info,"@");
$arrSent = str_getcsv($arrInfo[0],"-");
$bookId=$arrSent[0];
$para=$arrSent[1];
$begin=$arrSent[2];
$end=$arrSent[3];
$db_file = _DIR_PALICANON_TEMPLET_."/p".$bookId."_tpl.db3";	

PDO_Connect("sqlite:$db_file");
$query="SELECT * FROM 'main' WHERE (\"paragraph\" = ".$PDO->quote($para)." ) ";
$sth = $PDO->prepare($query);
$sth->execute();
$palitext="";
while($result = $sth->fetch(PDO::FETCH_ASSOC))
{
	$index =$result["wid"];
	if($index>=$begin && $index<=$end){
		if($result["type"]!=".ctl."){
			$paliword=$result["word"];
			if($result["style"]=="bld"){
				if(strchr($result["word"],"{")!=FALSE && strchr($result["word"],"}")!=FALSE ){
					$paliword = str_replace("{","<strong>",$paliword);
					$paliword = str_replace("}","</strong>",$paliword);
				}
				else{
					$paliword = "<strong>{$paliword}</strong>";
				}
			}
			$palitext .= $paliword." ";
		}
	}
}
$para_path=_get_para_path($bookId,$para);
//find out translation
$tran="";
$db_file=_FILE_DB_SENTENCE_;
try{
	PDO_Connect("sqlite:$db_file");
	$query="SELECT * FROM sentence WHERE book='{$bookId}' AND paragraph='{$para}' AND begin='{$begin}' AND end='{$end}'  AND text <> '' order by modify_time DESC limit 0 ,1 ";
	$Fetch = PDO_FetchAll($query);
	$iFetch=count($Fetch);
	if($iFetch>0){
		$tran = $Fetch[0]["text"];
	}
}
catch (Exception $e) {
	$tran = $e->getMessage();
    //echo 'Caught exception: ',  $e->getMessage(), "\n";
}
		

$output=array("id"=>$id,"palitext"=>$palitext,"tran"=>$tran,"ref"=>$para_path,"tran_count"=>$iFetch);
echo json_encode($output, JSON_UNESCAPED_UNICODE);
