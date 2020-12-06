<?php
require_once "../public/_pdo.php";
require_once "../path.php";

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
echo " $from \n";


if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}
if($to==0 || $to>=$fileNums) $to=$fileNums-1;

	$db_file = _FILE_DB_PAGE_INDEX_;
	PDO_Connect("sqlite:$db_file");
for($iFile=$from;$iFile<=$to;$iFile++){
    echo "{$iFile}\n";
	$FileName=$filelist[$iFile][1].".htm";
	$fileId=$filelist[$iFile][0];

	$inputFileName=$FileName;
	$outputFileNameHead=$filelist[$iFile][1];
	$bookId=$filelist[$iFile][2];

	$dirXml=$outputFileNameHead."/";

	$xmlfile = $inputFileName;
	echo "doing:".$xmlfile."\n";
	$log=$log."$iFile,$FileName,open\r\n";

	$arrInserString=array();


	// 打开文件并读取数据
	$irow=0;
	if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead.".csv", "r"))!==FALSE){
		// 开始一个事务，关闭自动提交
		$PDO->beginTransaction();
		$query="INSERT INTO cs6_para ('book','para','bookid','cspara') VALUES (  ? , ? , ? , ? )";
		$stmt = $PDO->prepare($query);

		$currCSPara=0;
		$currPara = 0;
		$iCount = 0;


		// 提交更改 
		try{
			while(($data=fgetcsv($fp,0,','))!==FALSE){
				$irow++;
				if($irow>1){
					if($data[6]==".ctl." && mb_substr($data[5],0,4,"UTF-8")=="para"){
						$currCSPara= (int)mb_substr($data[5],4,NULL,"UTF-8");
					}
					$para = $data[3];
					$arr = explode("-",$currCSPara);
					if($currPara != $data[3] && $data[6] != ".ctl."){
						$currPara=(int)$data[3];
						$book = mb_substr($data[2],1, null ,"UTF-8");
						$para = $currPara;
						$csPara = $currCSPara;
						$book2 = 0;
						if(count($arr)>1){
							$iBegin = (int)$arr[0];
							$iEnd = (int)$arr[1];
							
						}
						else{
							$iBegin = (int)$arr[0];
							$iEnd = (int)$arr[0];						
						}
						if($iEnd<$iBegin){
							$iEnd =(int)(mb_substr($arr[0],0,0-mb_strlen($arr[1])).$arr[1]);
						}
						$arr1=array();
						for($i=$iBegin; $i<=$iEnd; $i++){
							$arr1[] = $i;
						}
						foreach ($arr1 as $key => $value) {
							$stmt->execute(array($book,$para,$book2,$value));
							$iCount++;
						}
						
					}
				}
					
			}
			$PDO->commit();
		}catch (Exception $e){
			var_dump($e);
			$PDO->rollback();
		}

		if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
			$error = PDO_ErrorInfo();
			echo "error - $error[2] \n";
			$log.="$from, $FileName, error, $error[2] \r\n";
		}
		else{
			echo "updata  recorders.\n";
			$log.="updata  recorders.\r\n";
		}			

		fclose($fp);
		echo "单词表load：".$dirXmlBase.$dirXml.$outputFileNameHead.".csv\n";
	}
	else{
		echo "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv";
	}
	
}
	echo "齐活！功德无量！all done!";	
?>



</body>
</html>
