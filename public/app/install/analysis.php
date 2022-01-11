<?php
require_once "install_head.php";

$filelist=array();
$fileNums=0;
if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}
$arrAllPaliWordsCount=array();

$dirBase="analysis/";
$outputDir="analysis/";
$g_paliWordCounter=0;
$wordCountCsvHead=array("词","数量","百分比");
for($i=0;$i<$fileNums;$i++){
	$outputFileNameHead=$filelist[$i][1];
	$inputFileName[$i]=$dirBase.$outputFileNameHead.".csv";
	if(($handle=fopen($inputFileName[$i],'r'))!==FALSE){
		$wordCountCsvHead[$i*2+3]=$inputFileName[$i];
		while(($data=fgetcsv($handle,0,','))!==FALSE){
				$arrAllPaliWordsCount[$data[1]][0]=$data[1];
				if(count($arrAllPaliWordsCount[$data[1]])>1){
					$arrAllPaliWordsCount[$data[1]][1]+=$data[2];
					//$arrAllPaliWordsCount[$data[1]][$i*2+2]=$data[2];
					//$arrAllPaliWordsCount[$data[1]][$i*2+3]=$data[3];
				}
				else{
					$arrAllPaliWordsCount[$data[1]][1]=$data[2];
					//$arrAllPaliWordsCount[$data[1]][$i*2+2]=$data[2];
					//$arrAllPaliWordsCount[$data[1]][$i*2+3]=$data[3];
				}
				$g_paliWordCounter+=$data[2];
		}
	}
	else{
		echo "open file:".$inputFileName[$i]."false<br>";
	}
	fclose($handle);
}

$outputfile=$outputDir."all_count.csv";
/*Pali单词统计表*/
if(($fp=fopen($outputfile, "w"))!==FALSE){

	fputcsv($fp,$wordCountCsvHead);
	$i=0;
	$iLastRate=0.0;
	foreach($arrAllPaliWordsCount as $x=>$x_value){
		$x_value[2]=$x_value[1]*10000/$g_paliWordCounter;

		fputcsv($fp,$x_value);
	}
	fclose($fp);

	echo "Pali单词表统计导出到："."_count_pali.csv<br>";
}
else{
	echo "can not open csv file. filename="."_count.csv";
}


?>