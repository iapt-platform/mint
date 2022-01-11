<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<p><a href="index.php">Home</a></p>
<h2>合并以书为单位的单词表</h2>
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

$outputfile=$dirXmlBase."all_word_book.csv";
if(($fpoutput=fopen($outputfile, "w"))!==FALSE){
	$wordCountCsvHead=array("book","word","count","prsent","length");
	fputcsv($fpoutput,$wordCountCsvHead);
}
else{
	echo "can not open csv file. filename="."_count.csv";
}

$g_paliWordCounter=0;

for($i=0;$i<count($filelist);$i++)
{
	$bookid=$filelist[$i][2];
	$outputFileNameHead=$filelist[$i][1];
	$dirXml=$outputFileNameHead."/";
	$inputFileName=$dirXmlBase.$dirXml.$outputFileNameHead."_analysis.csv";
	echo "doing:[$i] - $outputFileNameHead <br />";
	if(($handle=fopen($inputFileName,'r'))!==FALSE){
		$iLineNum=0;
		while(($data=fgetcsv($handle,0,','))!==FALSE){
			if($iLineNum>0){/*skip first line*/	
				$data[0]=$bookid;
				fputcsv($fpoutput,$data);
			}
			$iLineNum++;
		}
	}
	else{
		echo "open file:".$inputFileName." false<br>";
	}
	fclose($handle);
}

fclose($fpoutput);

echo "all done!";
?>

</body>
</html>

