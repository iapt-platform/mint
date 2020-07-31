<?php
include "./config.php";
$csvDir = $dir_mydocument;
$csvFileName = $csvDir.$_GET["filename"];
$outXml = "<wordlist>";
$rowCount=0;
if(($handle=fopen($csvFileName,'r'))!==FALSE){
	while(($data=fgetcsv($handle,0,','))!==FALSE){
		if($rowCount>0){
				$outXml = $outXml."<word>";
				$outXml = $outXml."<pali>".$data[0]."</pali>";
				$outXml = $outXml."<real>".$data[1]."</real>";
				$outXml = $outXml."<id>".$data[2]."</id>";
				$outXml = $outXml."<mean>".$data[3]."</mean>";
				$outXml = $outXml."<org>".$data[4]."</org>";
				$outXml = $outXml."<om>".$data[5]."</om>";
				$outXml = $outXml."<case>".$data[6]."</case>";
				$outXml = $outXml."<bmc>".$data[7]."</bmc>";
				$outXml = $outXml."<bmt>".$data[8]."</bmt>";
				$outXml = $outXml."<note>".$data[9]."</note>";
				$outXml = $outXml."<lock>".$data[10]."</lock>";
				$outXml = $outXml."</word>";
		}
		$rowCount++;
	}
}
$outXml = $outXml."</wordlist>";
echo $outXml;

?>