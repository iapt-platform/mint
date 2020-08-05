<!--句子库生成-->
<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Insert to Sentence DB</h2>
<p><a href="index.php">Home</a></p>
<?php
include "./_pdo.php";
require_once '../path.php';

$db_file =_FILE_DB_PALI_SENTENCE_;
$thisfile = '.'.mb_substr(__FILE__,mb_strlen(__DIR__));
if(isset($_GET["from"])==false){
?>
<form action="<?php echo $thisfile; ?>" method="get">
From: <input type="text" value="0" name="from"><br>
To: <input type="text" value="216" name="to"><br>
<input type="submit">
</form>
<?php

		PDO_Connect("sqlite:$db_file");

		
		$query="CREATE TABLE pali_sent (
    id        INTEGER PRIMARY KEY AUTOINCREMENT,
    book      INTEGER,
    paragraph INTEGER,
    [begin]   INTEGER,
    [end]     INTEGER,
    length    INTEGER,
    count     INTEGER,
    text      TEXT,
    real      TEXT,
    real_en   TEXT
)";
		$stmt = @PDO_Execute($query);
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			$error = PDO_ErrorInfo();
			print_r($error[2]);
		}
		else{
			echo "create table pali_sent .";
		}
/*
		$query="CREATE INDEX 'search' ON \"pali_sent\" (\"text\", \"real\", \"real_en\" ASC)";
		$stmt = @PDO_Execute($query);
		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			$error = PDO_ErrorInfo();
			print_r($error[2]);
			$log=$log."$from, $FileName, error, $error[2] \r\n";
		}
*/	

return;
}

$from=$_GET["from"];
$to=$_GET["to"];
$filelist=array();
$fileNums=0;
$log="";
echo "<h2>$from-$to</h2>";

if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}
if($to>=$fileNums) $to=$fileNums-1;

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

$dirXmlBase=_DIR_PALI_CSV_."/";
$dirXml=$outputFileNameHead."/";

$currChapter="";
$currParNum="";
$arrAllWords[0]=array("id","wid","book","paragraph","word","real","type","gramma","mean","note","part","partmean","bmc","bmt","un","style","vri","sya","si","ka","pi","pa","kam");
$g_wordCounter=0;

$arrUnWords[0]=array("id","word","type","gramma","parent","mean","note","part","partmean","cf","state","delete","tag","len");
$g_unWordCounter=0;

$arrUnPart[0]="word";
$g_unPartCounter=-1;

/*去掉标点符号的统计*/
$arrAllPaliWordsCount=array();
$g_paliWordCounter=0;
$g_wordCounterInSutta=0;
$g_paliWordCountCounter=0;


$xmlfile = $inputFileName;
echo "doing:".$xmlfile."<br>";
$log=$log."$from,$FileName,open\r\n";

$arrInserString=array();

function getWordEn($strIn){
	$search  = array('ā', 'ī', 'ū', 'ṅ', 'ñ' , 'ṭ', 'ḍ', 'ṇ', 'ḷ', 'ṃ');
	$replace = array('a', 'i', 'u', 'n', 'n' , 't', 'd', 'n', 'l', 'm');
	return(str_replace($search,$replace,$strIn));
}



// 打开文件并读取数据
$iWord=0;
$pre=null;
$curr=null;
$next=null;
$wordlist=array();
$arrSent=array();
$book=0;
if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead.".csv", "r"))!==FALSE){
	while(($data=fgetcsv($fp))!==FALSE){
		//id,wid,book,paragraph,word,real,type,gramma,mean,note,part,partmean,bmc,bmt,un,style,vri,sya,si,ka,pi,pa,kam
		//$data = mb_split(",",$data);
		
		$wordlist[]=$data;
		if($book==0){
			$book=substr($data[2],1);
		}

	}
	fclose($fp);
	
	$iWord=0;
	$iCurrPara=0;
	$Note_Mark=0;
	if($wordlist[1][6]!=".ctl."){
		$sent=$wordlist[1][4]." ";
		$sent_real=$wordlist[1][5];
		$wordcount=1;
	}
	else{
		$sent="";
		$sent_real="";
		$wordcount=0;
	}
	$begin=1;
	$end=1;
	$iSent=0;
	
	for($i=2;$i<count($wordlist);$i++){
		if($wordlist[$i][3]>$iCurrPara){
			//echo  "new paragraph<br>";
			$iWord=0;
			if($i>2){
				//echo "上一段结束<br>";
				if(strlen(trim($sent))>0){
					$end = $wordlist[$i-1][16];
					$arrSent[]=array($book,$iCurrPara,$begin,$end,mb_strlen($sent_real,"UTF-8"),$wordcount,$sent,$sent_real,getWordEn($sent_real));
					//echo "end={$end}<br>";
					//echo "<div>[{$iCurrPara}-{$begin}-{$end}]({$wordcount})<br>{$sent}<br>{$sent_real}<br>".getWordEn($sent_real)."</div>";
				}
				$iCurrPara=$wordlist[$i][3];
				//下一段开始
				if($wordlist[$i][6]!=".ctl."){
					$sent=$wordlist[$i][4]." ";
					if($wordlist[$i][5]=='"'){
						$sent_real="";
					}
					else{
						$sent_real=$wordlist[$i][5];
					}
					$wordcount=1;
				}
				else{
					$sent="";
					$sent_real="";
					$wordcount=0;
				}
				$begin = $wordlist[$i][16];
				
				$iSent++;
				
				continue;
			}
			$iCurrPara=$wordlist[$i][3];
		}
		$isEndOfSen=false;
		if($i<count($wordlist)-1){
			$pre=$wordlist[$i-1];
			$curr=$wordlist[$i];
			$next=$wordlist[$i+1];
			if($curr[5]!=""){
				$wordcount++;
			}
			if($next[4]=="("){
				$Note_Mark=1;
			}
			else if($pre[4]==")" && $Note_Mark==1){
				$Note_Mark=0;
			}

			if($curr[15]!="note" || mb_substr($curr[1],0,5,"UTF-8")!="gatha"){
				if($curr[4]=="."  && !is_numeric($pre[4]) && $next[3]==$iCurrPara && $Note_Mark==0){//以.結尾且非註釋
					if($next[4]!="("){
						$isEndOfSen=true;
					}
				}
				else if($curr[4]=="–"  && $next[4]=="‘"  && $Note_Mark==0){
					$isEndOfSen=true;
				}
				else if($Note_Mark==0){//以!或?或;結尾
					if($curr[4]=="!"){
						if($next[4]!="!"){
							if($next[4]!="("){
								$isEndOfSen=true;
							}
						}
					}
					else if($curr[4]==";" || $curr[4]=="?"){
						if($next[4]!="("){
							$isEndOfSen=true;
						}
					}
				}
			}
		}

			if($curr[6]!=".ctl."){
				if($next[5]!=""){
					$sent .= $curr[4]." ";
				}
				else{
					$sent .= $curr[4];
				}
				if($wordlist[$i][5]!='"'){
					if($wordlist[$i][5]=="iti"){
						$sent_real .=$curr[4];
					}
					else{
						$sent_real .=$curr[5];
					}
				}
			}
			if($isEndOfSen==true && strlen(trim($sent))>0){
				$end = $wordlist[$i][16];
				$arrSent[]=array($book,$iCurrPara,$begin,$end,mb_strlen($sent_real,"UTF-8"),$wordcount,$sent,$sent_real,getWordEn($sent_real));			
				//echo "end={$end}<br>";
				//echo "<div>[{$iCurrPara}-{$begin}-{$end}]({$wordcount})<br>{$sent}<br>{$sent_real}<br>".getWordEn($sent_real)."</div>";
				
				$sent="";
				$sent_real="";
				$iSent++;
				$begin = $wordlist[$i][16]+1;
				$wordcount=0;
			}
			
			$iWord++;
	}
				if(strlen(trim($sent))>0){
					$end = $wordlist[count($wordlist)-1][16];
					$arrSent[]=array($book,$iCurrPara,$begin,$end,mb_strlen($sent_real,"UTF-8"),$wordcount,$sent,$sent_real,getWordEn($sent_real));
					//echo "end={$end}<br>";
					//echo "<div>[{$iCurrPara}-{$begin}-{$end}]({$wordcount})<br>{$sent}<br>{$sent_real}<br>".getWordEn($sent_real)."</div>";
				}
}
else{
	echo "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv";
}

// 开始一个事务，关闭自动提交

PDO_Connect("sqlite:$db_file");
$PDO->beginTransaction();
$query="INSERT INTO pali_sent ('id','book','paragraph','begin','end','length','count','text','real','real_en') VALUES (NULL,?,?,?,?,?,?,?,?,?)";
$stmt = $PDO->prepare($query);
foreach($arrSent as $oneParam){
	$stmt->execute($oneParam);
}
// 提交更改 
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	echo "error - $error[2] <br>";
	
	$log=$log."$from, $FileName, error, $error[2] \r\n";
}
else{
	$count=count($arrSent);
	echo "updata $count recorders.";
}

	$myLogFile = fopen(_DIR_LOG_."insert_sent.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);

?>


<?php 

if($from>=$to){
	echo "<h2>齐活！功德无量！all done!</h2>";
}
else{
	echo "<script>";
	echo "window.location.assign(\"db_insert_sentence.php?from=".($from+1)."&to=".$to."\")";
	echo "</script>";
	echo "正在载入:".($from+1)."——".$filelist[$from+1][0];
}
?>
</body>
</html>
