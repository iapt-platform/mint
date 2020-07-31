<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Update Pali Text To single DB</h2>
<p><a href="index.php">Home</a></p>
<?php
include "../public/_pdo.php";
if(isset($_GET["from"])==false){
?>
<form action="db_update_palitext.php" method="get">
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

$dirLog="log/";

$dirDb="db/";
$inputFileName=$FileName;
$outputFileNameHead=$filelist[$from][1];
$bookId=$filelist[$from][2];
$vriParNum=0;
$wordOrder=1;

$dirXmlBase="xml/";
$dirPaliTextBase="pali-text/";
$dirXml=$outputFileNameHead."/";



$xmlfile = $inputFileName;
echo "doing:".$xmlfile."<br>";
$log=$log."$from,$FileName,open\r\n";

$arrInserString=array();



// 打开vri html文件并读取数据
$pali_text_array=array();//vri text
if(($fpPaliText=fopen($dirPaliTextBase.$xmlfile, "r"))!==FALSE){
	while(($data=fgets($fpPaliText))!==FALSE){
		array_push($pali_text_array,$data);
	}
	fclose($fpPaliText);
	echo "pali text load：".$dirPaliTextBase.$xmlfile."<br>";
}
else{
	echo "can not pali text file. filename=".$dirPaliTextBase.$xmlfile;
}
// 打开text文件并读取数据
$inputRow=0;
if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead."_pali.csv", "r"))!==FALSE){
	while(($data=fgetcsv($fp,0,','))!==FALSE){
		if($inputRow>0){
		//if(($inputRow-1)<count($pali_text_array)){
		//	$data[6]=$pali_text_array[$inputRow-1];
		//}
		$params=$data;
		array_push($arrInserString,$params);
		}
		$inputRow++;
	}
	fclose($fp);
	echo "单词表load：".$dirXmlBase.$dirXml.$outputFileNameHead.".csv<br>";
}
else{
	echo "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv";
}

if(($inputRow-1)!=count($pali_text_array)){
$log=$log."$from, $FileName,error,文件行数不匹配 inputRow=$inputRow pali_text_array=".count($pali_text_array)." \r\n";
}

$book=$from+1;
$db_file = "pali_text_new/p{$book}_pali.db3";
PDO_Connect("sqlite:$db_file");
$query = "select paragraph,level,text from data where 1";
echo $query;
$Fetch_old = PDO_FetchAll($query);


$db_file = $dirDb.'pali_text.db3';
PDO_Connect("sqlite:$db_file");
// 开始一个事务，关闭自动提交
$PDO->beginTransaction();
/*
$query ="UPDATE pali_text SET text = ? WHERE book=? and paragraph=?";
$stmt = $PDO->prepare($query);
foreach($arrInserString as $oneParam){
	$bookid=substr($oneParam[1],1);
	$newData=array($oneParam[6],
				   $bookid,
				   $oneParam[2]);
	$stmt->execute($newData);
}
*/
$query ="UPDATE pali_text SET level = ? , toc = ? WHERE book=? and paragraph=?";
$stmt = $PDO->prepare($query);
foreach($Fetch_old as $onePara){
	$level=$onePara["level"];
	if($level==0){
		$level=100;
	}
	if($level==100){
		$maxLen=40;
		if(mb_strlen($onePara["text"],"UTF-8")>$maxLen){
			$toc=ltrim(mb_substr($onePara["text"],0,$maxLen,"UTF-8"))."…";
		}
		else{
			$toc=ltrim($onePara["text"]);
		}
	}
	else{
		$toc=ltrim($onePara["text"]);
	}
	$newData=array($level,
				   $toc,
				   $book,
				   $onePara["paragraph"]);
	$stmt->execute($newData);
}

// 提交更改 
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	echo "error - $error[2] <br>";
	
	$log=$log."$from, $FileName, error, $error[2] \r\n";
}
else{
	$count=count($arrInserString);
	echo "updata $count recorders.";
}



	$myLogFile = fopen($dirLog."db_insert_palitext.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);
?>


<?php 
if($from==$to){
	echo "<h2>all done!</h2>";
}
else{
	echo "<script>";
	echo "window.location.assign(\"db_update_palitext.php?from=".($from+1)."&to=".$to."\")";
	echo "</script>";
	echo "正在载入:".($from+1)."——".$filelist[$from+1][0];
}
?>
</body>
</html>
