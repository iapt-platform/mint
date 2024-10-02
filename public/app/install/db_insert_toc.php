<?php
require_once "install_head.php";
?>
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>合并标题文件</h2>
<p><a href="index.php">Home</a></p>
<?php
include "./_pdo.php";
if(isset($_GET["from"])==false){
?>
<form action="db_insert_toc.php" method="get">
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

$xmlfile = $inputFileName;
echo "doing:".$xmlfile."<br>";
$log=$log."$from,$FileName,open\r\n";

$arrInserString=array();
$db_file = $dirDb.'toc.db3';
$row=0;
if(($fp_toc=fopen($dirDb."toc.csv", "a"))===FALSE){
	$error= "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv";
	echo $error;
	$log=$log."$from,$FileName,error, $error \r\n";
}
// 打开文件并读取数据
if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead."_toc.csv", "r"))!==FALSE){
	while(($data=fgetcsv($fp,0,','))!==FALSE){
		if($row>0){
			fputcsv($fp_toc,$data);
		}
		$row++;
	}
	fclose($fp);
	fclose($fp_toc);
	echo "Toc导出到：".$dirDb."toc.csv<br>";	
	echo "toc load：".$dirXmlBase.$dirXml.$outputFileNameHead."_toc.csv<br>";
}
else{
	$error= "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead."_toc.csv";
	echo $error;
	$log=$log."$from,$FileName,error, $error \r\n";
}

/* 开始一个事务，关闭自动提交 */
/*
$PDO->beginTransaction();
$query="INSERT INTO toc ('id','book','par_num','level','class','language','title','author','editor','modify','edition','sub_ver') VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);
foreach($arrInserString as $oneParam){
$stmt->execute($oneParam);
}
*/
/* 提交更改 */
/*
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



*/	
	$myLogFile = fopen($dirLog."insert_db_toc.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);	
?>


<?php 
if($from==$to){
	echo "<h2>齐活！功德无量！all done!</h2>";
}
else{
	echo "<script>";
	echo "window.location.assign(\"db_insert_toc.php?from=".($from+1)."&to=".$to."\")";
	echo "</script>";
	echo "正在载入:".($from+1)."——".$filelist[$from+1][0];
}
?>
</body>
</html>
