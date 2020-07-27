<?php
if(isset($_GET["code"])){
	$currCode=$_GET["code"];
}
else{
$currCode="rm";
}
$caseRowCounter=0;
$casetable = array();
$beginHead = false;
$endHead = false;
$strError = "";
$xmlOutput = "<script>";

if(($handle=fopen("script/$currCode.csv",'r'))!==FALSE){
	while(($data=fgetcsv($handle,0,','))!==FALSE){
		if(!$beginHead && !$endHead){
			if($data[0]=="#"){
				$xmlOutput = $xmlOutput."<info><title>".$data[1]."</title>";
				$beginHead = true;
			}
		}
		//in head
		if($beginHead && !$endHead){
			if($data[0]=="#"){
				$xmlOutput = $xmlOutput."</info><inner>";
				$endHead = true;
			}
			else{
				$xmlOutput = $xmlOutput."<".$data[0].">".$data[1]."</".$data[0].">";
			}
		}
		//in body
		if($beginHead && $endHead){
			$xmlOutput = $xmlOutput . "<word><src>".$data[0]."</src><dest>".$data[1]."</dest></word>";
		}
	}
	$xmlOutput = $xmlOutput . "</inner>";
}
else{
	$strError=$strError."can not open script/sinhala.csv ";
}

$xmlOutput = $xmlOutput . "<error>".$strError."</error>";
$xmlOutput = $xmlOutput . "</script>";

echo $xmlOutput;
?>