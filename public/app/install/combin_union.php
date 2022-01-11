<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<p><a href="index.php">Home</a></p>
<h2>合并连读词表</h2>
<?php
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
	$inputFileName=$dirXmlBase.$dirXml.$outputFileNameHead."_un.csv";
	echo "doing:[$i] - $outputFileNameHead <br />";
	if(($handle=fopen($inputFileName,'r'))!==FALSE){
		$iLineNum=0;
		while(($data=fgetcsv($handle,0,','))!==FALSE){
			if($iLineNum>0){/*skip first line*/	
			$pali=str_getcsv($data[7],'+')[0];
				if(isset($arrAllPaliWordsCount[$pali])){
					//if($arrAllPaliWordsCount[$pali][8]!=$data[2];
				}
				else{
					$arrAllPaliWordsCount[$pali]=$data;
				}
				$g_paliWordCounter++;
			}
			$iLineNum++;
		}
	}
	else{
		echo "open file:".$inputFileName." false<br>";
	}
	fclose($handle);
	

}

$outputfile=$dirXmlBase."all_union.csv";
echo "outputfile:".$outputfile."<br>";
/*union表*/
if(($fp=fopen($outputfile, "w"))!==FALSE){
	$wordCountCsvHead=array("id","word","type","gramma","parent","mean","note","part","partmean","cf","state","delete","tag","len");
	fputcsv($fp,$wordCountCsvHead);
	foreach($arrAllPaliWordsCount as $x=>$x_value){
		fputcsv($fp,$x_value);
	}
	fclose($fp);

	echo "union 导出到：".$outputfile.".csv<br>";
}
else{
	echo "can not open csv file. filename="."_count.csv";
}

echo "all done!";
?>

</body>
</html>

