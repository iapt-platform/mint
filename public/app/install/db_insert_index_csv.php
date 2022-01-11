<?php

require_once "install_head.php";
include "./_pdo.php";
if (PHP_SAPI  == "cli") {
	echo $argc;
	if($argc>=3){
		$from=$argv[1];
		$to=$argv[2];
		echo "From: {$from} To:{$to}";
	}
	else if($argc>=1){
		$from=0;
		$to = 216;
		echo "生成全部217本书";
	}
	else{
		echo "参数错误";
		exit;
	}

}
else{
	echo "<!DOCTYPE html><html><head></head>";
	echo "<body><h2>Insert to Index</h2>";

	if(isset($_GET["from"])==false){
		echo '<form action="db_insert_index_csv.php" method="get">';
		echo 'From: <input type="text" name="from" value="0"><br>';
		echo 'To: <input type="text" name="to" value="216"><br>';
		echo '<input type="submit">';
		echo '</form>';
		exit;
	}
	else{
		$from=$_GET["from"];
		$to=$_GET["to"];
	}
}


$g_wordCounter=0;
$g_wordIndexCounter=0;
$iAllWordIndex=array();
$sAllWord=array();

$dirLog=_DIR_LOG_."/";

$dirXmlBase=_DIR_PALI_CSV_."/";

$filelist=array();
$fileNums=0;
$log="";
echo "<h2>$from</h2>";
function getWordEn($strIn){
	$out=$strIn;
	$out=str_replace("ā","a",$out);
	$out=str_replace("ī","i",$out);
	$out=str_replace("ū","u",$out);
	$out=str_replace("ṅ","n",$out);
	$out=str_replace("ñ","n",$out);
	$out=str_replace("ṭ","t",$out);
	$out=str_replace("ḍ","d",$out);
	$out=str_replace("ṇ","n",$out);
	$out=str_replace("ḷ","l",$out);
	$out=str_replace("ṃ","m",$out);
	return($out);
}

if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}
if($to==0 || $to>=$fileNums) $to=$fileNums-1;

	
for($iFile=$from;$iFile<=$to;$iFile++){
    echo "<h3>{$iFile}</h3>";
	$FileName=$filelist[$iFile][1].".htm";
	$fileId=$filelist[$iFile][0];

	$inputFileName=$FileName;
	$outputFileNameHead=$filelist[$iFile][1];
	$bookId=$filelist[$iFile][2];

	$dirXml=$outputFileNameHead."/";

	$xmlfile = $inputFileName;
	echo "doing:".$xmlfile."<br>";
	$log=$log."$iFile,$FileName,open\r\n";

	$arrInserString=array();


	// 打开文件并读取数据
	$irow=0;
	if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead.".csv", "r"))!==FALSE){
		while(($data=fgetcsv($fp,0,','))!==FALSE){
			$irow++;
			if($irow>1){
				$params=$data;
				$arrInserString[count($arrInserString)]=$params;
			}
		}
		fclose($fp);
		echo "单词表load：".$dirXmlBase.$dirXml.$outputFileNameHead.".csv<br>";
	}
	else{
		echo "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv";
	}
	

	if(($fpoutput=fopen(_DIR_CSV_PALI_CANON_WORD_."/{$iFile}_words.csv", "w"))!==FALSE){
		//$query="INSERT INTO word ('id','book','paragraph','wordindex','bold') VALUES (?,?,?,?,?)";
		$count=0;
		$count1=0;
		$sen="";
		$sen1="";
		$sen_en="";
		$sen_count=0;
		$book="";
		$paragraph="";
		foreach($arrInserString as $oneParam){	
			if($oneParam[5]!=""){
				$g_wordCounter++;
				$book=substr($oneParam[2],1);
				$paragraph=$oneParam[3];
				$word=$oneParam[5];
				if($oneParam[15]=="bld" ){
					$bold=1;
				}
				else{
					$bold=0;
				}			
			
				if(isset($sAllWord[$word])){
					$wordindex=$sAllWord[$word];
					
					$iAllWordIndex[$wordindex][1]++;
					if($bold==1){
						$iAllWordIndex[$wordindex][3]++;
					}
					else{
						$iAllWordIndex[$wordindex][2]++;
					}
				}
				else{
					$wordindex=$g_wordIndexCounter;
					$sAllWord[$word]=$g_wordIndexCounter;
					
					$iAllWordIndex[$g_wordIndexCounter][0]=$word;
					
					$iAllWordIndex[$g_wordIndexCounter][1]=1;//all word count
					if($bold==1){
						$iAllWordIndex[$g_wordIndexCounter][2]=0;
						$iAllWordIndex[$g_wordIndexCounter][3]=1;
					}
					else{
						$iAllWordIndex[$g_wordIndexCounter][2]=1;
						$iAllWordIndex[$g_wordIndexCounter][3]=0;
					}
					
					$g_wordIndexCounter++;
				}
		
				
				$newWord=array($g_wordCounter,$book,$paragraph,$wordindex,$bold);
				fputcsv($fpoutput,$newWord);
				$count++;
			}

		}
		fclose($fpoutput);
	}
	else{
		echo "open file false";
	}

}



	//$query="INSERT INTO wordindex ('id','word','word_en','count','normal','bold','is_base','len') VALUES (?,?,?,?,?,?,?,?)";

	if(($fpoutput=fopen(_DIR_CSV_PALI_CANON_WORD_INDEX_."/0.csv", "w"))!==FALSE){	
		echo count($iAllWordIndex)."words<br>";
		for($iword=0;$iword<count($iAllWordIndex);$iword++){
			if(($iword % 10000)==0){
				fclose($fpoutput);
				$fpoutput=fopen(_DIR_CSV_PALI_CANON_WORD_INDEX_."/" . ($iword/10000) . ".csv", "w");
			}
			$wordindex=$iword;
			$newWord=array($wordindex,$iAllWordIndex[$iword][0],getWordEn($iAllWordIndex[$iword][0]),$iAllWordIndex[$iword][1],$iAllWordIndex[$iword][2],$iAllWordIndex[$iword][3],0,mb_strlen($iAllWordIndex[$iword][0],"UTF-8"));
			fputcsv($fpoutput,$newWord);
		}
		fclose($fpoutput);
	}
	else{
		echo "can not open file ";
	}
	
	$myLogFile = fopen($dirLog."insert_index.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);
	
	
	echo "<h2>齐活！功德无量！all done!</h2>";	
?>



</body>
</html>
