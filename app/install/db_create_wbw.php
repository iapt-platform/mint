<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Create WBW Databse</h2>
<p><a href="index.php">Home</a></p>
<?php
include "./_pdo.php";
if(isset($_GET["from"])==false){
?>
<form action="db_create_wbw.php" method="get">
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

$dirDb="db/wbw/";
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
$db_file = $dirDb.$bookId.'_wbw.db3';
PDO_Connect("sqlite:$db_file");

$query="CREATE TABLE 'main' ( 'id' TEXT PRIMARY KEY NOT NULL, 'wid' TEXT, 'book' TEXT, 'paragraph' INTEGER, 'word' TEXT, 'real' TEXT, 'type' TEXT, 'gramma' TEXT, 'mean' TEXT, 'note' TEXT, 'part' TEXT, 'partmean' TEXT, 'bmc' INTEGER, 'bmt' TEXT, 'un' TEXT, 'style' TEXT, 'language'  TEXT, 'author' TEXT, 'editor' TEXT, 'revision' TEXT, 'edition' INTEGER, 'subver' INTEGER,'time' DATETIME DEFAULT CURRENT_TIMESTAMP, 'vri' INTEGER, 'sya' INTEGER, 'si' INTEGER, 'ka' INTEGER, 'pi' INTEGER, 'pa' INTEGER, 'kam' INTEGER )";
    $stmt = @PDO_Execute($query);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        print_r($error[2]);
        break;
    }

$query="CREATE INDEX 'search' ON \"main\" (\"book\", \"paragraph\", \"vri\" ASC)";
    $stmt = @PDO_Execute($query);
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        print_r($error[2]);
        $log=$log."$from, $FileName, error, $error[2] \r\n";
    }

	$myLogFile = fopen($dirLog."insert_db.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);
?>


<?php 
if($from==$to){
	echo "<h2>all done!</h2>";
}
else{
	echo "<script>";
	echo "window.location.assign(\"db_create_wbw.php?from=".($from+1)."&to=".$to."\")";
	echo "</script>";
	echo "正在载入:".($from+1)."——".$filelist[$from+1][0];
}
?>
</body>
</html>
