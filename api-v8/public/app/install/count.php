<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<p><a href="index.php">Home</a></p>
<?php
//生成巴利语单词统计表
$dirXmlBase="xml/";

$filelist=array();
$fileNums=0;
$log="";
if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}

$g_paliWordCounter=0;

for($i=0;$i<count($filelist);$i++)
{
	$outputFileNameHead=$filelist[$i][1];
	$dirXml=$outputFileNameHead."/";
	$inputFileName=$dirXmlBase.$dirXml.$outputFileNameHead."_analysis.csv";
	echo "doing:[$i] - $inputFileName <br />";
	if(($handle=fopen($inputFileName,'r'))!==FALSE){
		$iLineNum=0;
		while(($data=fgetcsv($handle,0,','))!==FALSE){
			if($iLineNum>0){/*skip first line*/	
			$pali=$data[1];
				if(isset($arrAllPaliWordsCount[$pali])){
					$arrAllPaliWordsCount[$pali][1]+=$data[2];
				}
				else{
					$arrAllPaliWordsCount[$pali][0]="";
					$arrAllPaliWordsCount[$pali][1]=$data[2];
					
				}
				$g_paliWordCounter+=$data[2];
			}
			$iLineNum++;
		}
	}
	else{
		echo "open file:".$inputFileName." false<br>";
	}
	fclose($handle);
	
//union part
	$inputFileName=$dirXmlBase.$dirXml.$outputFileNameHead."_un_part.csv";
	if(($handle=fopen($inputFileName,'r'))!==FALSE){
		$iLineNum=0;
		while(($data=fgetcsv($handle,0,','))!==FALSE){
			$pali=$data[0];
				if(isset($arrAllPaliWordsCount[$pali])){
				}
				else{
					$arrAllPaliWordsCount[$pali][0]="";
					$arrAllPaliWordsCount[$pali][1]=0;
				}


			$iLineNum++; 
		}
	}
	else{
		echo "open file:".$inputFileName." false<br>";
	}
	fclose($handle);
}

$outputfile=$dirXmlBase."all_word.csv";
echo "outputfile:".$outputfile."<br>";
/*Pali单词统计表*/
if(($fp=fopen($outputfile, "w"))!==FALSE){
	$wordCountCsvHead=array("编号","拼写","数量","万分比","长度","状态");
	
	fputcsv($fp,$wordCountCsvHead);
	$i=0;
	$iLastRate=0.0;
	foreach($arrAllPaliWordsCount as $x=>$x_value){
		$i++;
		$csvWord[0]=$i;
		$csvWord[1]=$x;
		$csvWord[2]=$x_value[1];
		if($x_value[1]>0){
			$csvWord[3]=$x_value[1]*10000/$g_paliWordCounter;
		}
		else{
			$csvWord[3]=0;
		}
		$csvWord[4]=mb_strlen($x,"UTF-8");
		
		$csvWord[5]=100;

		fputcsv($fp,$csvWord);
	}
	fclose($fp);

	echo "Pali单词表统计导出到：".$outputfile.".csv<br>";
}
else{
	echo "can not open csv file. filename="."_count.csv";
}

echo "all done!";
?>

</body>
</html>

