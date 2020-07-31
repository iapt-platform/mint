<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>提取para?_??的内容</h2>
<p><a href="index.php">Home</a></p>
<?php

$thisFileName=basename(__FILE__);

if(isset($_GET["from"])==false){
?>
<form action="<?php echo $thisFileName;?>" method="get">
From: <input type="text" name="from"><br>
To: <input type="text" name="to"><br>
<input type="submit">
</form>
<?php
return;
}

$from=$_GET["from"];
$to=$_GET["to"];
$filelist=array();
$fileNums=0;
$log="";
$dirLog="log/";
$dirDb="db/";


if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}
if($to==0 || $to>=$fileNums) $to=$fileNums-1;

$outputFile = fopen("xml/book_link.csv", "a") or die("Unable to open file!");
$aBook = array();

for($iFile=$from;$iFile<$to;$iFile++){
	echo "<h2>$iFile</h2>";
	$FileName=$filelist[$iFile][1].".htm";
	$fileId=$filelist[$iFile][0];

	$inputFileName=$FileName;
	$outputFileNameHead=$filelist[$iFile][1];
	$bookId=$filelist[$iFile][2];
	$vriParNum=0;
	$wordOrder=1;

	$dirXmlBase="xml/";
	$dirXml=$outputFileNameHead."/";

	$currParNum="";

	$xmlfile = $inputFileName;
	echo "doing:".$xmlfile."<br>";
	$log=$log."$from,$FileName,open\r\n";


	// 打开文件并读取数据
	$strOutput="";
	$Begin=false;
	$count=0;

	if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead.".csv", "r"))!==FALSE){
		while(($data=fgetcsv($fp,0,','))!==FALSE){
			if($data[7]==".a."){
				if(substr($data[4],0,4)=="para"){
					if($bookid=stristr($data[4],"_")){
						$bookid=substr($bookid,1);
						$aBook["{$bookid}"]=1;
					}
				}
			}			
		}
		fclose($fp);
		echo "单词表load：".$dirXmlBase.$dirXml.$outputFileNameHead.".csv<br>";
	}
	else{
		echo "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv";
	}
}

/*
	$myLogFile = fopen($dirLog."insert_db.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);
	*/
	fclose($outputFile);
	
	echo "count:".count($aBook)."<br>";
	foreach($aBook as $x=>$value){
		echo "{$x}<br>";
	}

	echo "<h2>all done!</h2>";

?>
</body>
</html>
