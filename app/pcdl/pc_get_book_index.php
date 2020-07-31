<?php
$currBook=$_GET["book"];

include "./config.php";
include "./_pdo.php";
$countInsert=0;
$wordlist=array();

$outXml = "<index>";
echo $outXml;

	//$db_file = "../appdata/palicanon/templet/toc.db3";
	$db_file = "../appdata/palicanon/pali_text/".$currBook."_pali.db3";
		//open database
	PDO_Connect("sqlite:$db_file");
		$query = "select * FROM data where \"book\"=".$PDO->quote($currBook);

		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);

		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				$outXml = "<paragraph>";
				$outXml = $outXml."<book>".$Fetch[$i]["book"]."</book>";
				$outXml = $outXml."<par>".$Fetch[$i]["paragraph"]."</par>";
				$outXml = $outXml."<level>".$Fetch[$i]["level"]."</level>";
				$outXml = $outXml."<class>".$Fetch[$i]["class"]."</class>";
				//if($Fetch[$i]["level"]==0)
				{
					$outXml = $outXml."<title>".mb_substr($Fetch[$i]["text"],0,50,"UTF-8")."</title>";
				}
				//else{
				//	$outXml = $outXml."<title>".$Fetch[$i]["text"]."</title>";
				//}
				$outXml = $outXml."<language>".$Fetch[$i]["language"]."</language>";
				//$outXml = $outXml."<author>".$Fetch[$i]["author"]."</author>";
				$outXml = $outXml."<edition>".$Fetch[$i]["edition"]."</edition>";
				$outXml = $outXml."<subver></subver>";
				$outXml = $outXml."</paragraph>";			
				echo $outXml;
			}
		}
/*查询结束*/


$outXml = "</index>";
echo $outXml;
?>