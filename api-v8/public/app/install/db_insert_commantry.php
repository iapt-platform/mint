<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>提取小括号里的内容</h2>
<p><a href="index.php">Home</a></p>
<?php
include "./_pdo.php";
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
echo "<h2>$from</h2>";

if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}
if($to==0 || $to>=$fileNums) $to=$fileNums-1;

$FileName=$filelist[$from][1].".htm";
$fileId=$filelist[$from][0];
$fileId=$filelist[$from][0];

$dirLog=_DIR_LOG_."/";

$dirDb="db/";
$inputFileName=$FileName;
$outputFileNameHead=$filelist[$from][1];
$bookId=$filelist[$from][2];
$vriParNum=0;
$wordOrder=1;

$dirXmlBase="xml/";
$dirXml=$outputFileNameHead."/";

$currParNum="";



$xmlfile = $inputFileName;
echo "doing:".$xmlfile."<br>";
$log=$log."$from,$FileName,open\r\n";


$outputFile = fopen("xml/commantry.csv", "a") or die("Unable to open file!");


// 打开文件并读取数据
$strOutput="";
$Begin=false;
$count=0;
if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead.".csv", "r"))!==FALSE){
	while(($data=fgetcsv($fp,0,','))!==FALSE){
		if($data[15]=="paranum"){
			$currParNum=$data[4];
		}
		if($data[4]=="("){
			$Begin=true;
		}
		else if($data[4]==")"){
			$book=substr($data[2],1);
			$strOutput = str_replace(" .",".",$strOutput);
			fwrite($outputFile, "\"{$book}\",\"{$currParNum}\",\"{$data[3]}\",\"{$strOutput}\"\r\n");
			$Begin = false;
			$strOutput="";
			$count++;
		}
		else{
			if($Begin){
				$strOutput.=$data[4];
			}
		}
		
	}
	fclose($fp);
	echo "单词表load：".$dirXmlBase.$dirXml.$outputFileNameHead.".csv<br>";
}
else{
	echo "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv";
}


/*
	$myLogFile = fopen($dirLog."insert_db.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);
	*/
	fclose($outputFile);
	echo "count:$count";
?>


<?php 
if($from==$to){
	echo "<h2>齐活！功德无量！all done!</h2>";
}
else{
	echo "<script>";
	echo "window.location.assign(\"{$thisFileName}?from=".($from+1)."&to=".$to."\")";
	echo "</script>";
	echo "正在载入:".($from+1)."——".$filelist[$from+1][0];
}
?>
</body>
</html>
