<?php
# 用拆分好的三藏数据 导出cs6段落编号
require_once '../public/_pdo.php';
require_once '../config.php';
if ($argc < 3){
	echo "无效的参数 ";
	exit;
}
$from = (int)$argv[1];
$to =(int)$argv[2];
if($from<1){
	$from = 1;
}
if($to>217){
	$to = 217;
}


$filelist=array();
$fileNums=0;
$log="";
$dirLog=_DIR_LOG_."/";
$dirDb="db/";


if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($filelist[$fileNums]=fgetcsv($handle,0,','))!==FALSE){
		$fileNums++;
	}
}

$outputFile = fopen(_DIR_PALI_CSV_."/book_cs6_para.csv", "w") or die("Unable to open file!");
$aBook = array();

for($iFile=$from-1;$iFile<=$to-1;$iFile++){
	echo "doing $iFile ";
	$FileName=$filelist[$iFile][1].".htm";
	$fileId=$filelist[$iFile][0];

	$inputFileName=$FileName;
	$outputFileNameHead=$filelist[$iFile][1];
	$bookId=$filelist[$iFile][2];
	$vriParNum=0;
	$wordOrder=1;

	$dirXmlBase=_DIR_PALI_CSV_."/";
	$dirXml=$outputFileNameHead."/";

	$currParNum="";

	$xmlfile = $inputFileName;
	# $log=$log."$from,$FileName,open\r\n";


	// 打开文件并读取数据
	$strOutput="";
	$Begin=false;
	$count=0;

	if(($fp=fopen($dirXmlBase.$dirXml.$outputFileNameHead.".csv", "r"))!==FALSE){
		while(($data=fgetcsv($fp,0,','))!==FALSE){
			if($data[7]==".a."){
				if(stripos($data[4],"para")!==false){
					if($bookid=stristr($data[4],"_")){
						$bookid=substr($bookid,1);
						$paraString = stristr($data[4],"_",true);
						$paraBegin = stripos($paraString,"para")+4;
						$paraNum = explode("-",substr($paraString,$paraBegin));
						$count++;
						$output = array();
						$output[] = substr($data[2],1);
						$output[] = $data[3];
						$output[] = $bookid;

						foreach ($paraNum as $key => $value) {
							# code...
							$output[] = $value;
						}
						if(count($paraNum)==1){
							$output[] = $paraNum[0];
						}
						fputcsv($outputFile,$output);
					}
				}
			}
		}
		fclose($fp);
		echo "$count \n";
	}
	else{
		echo "can not open csv file. filename=".$dirXmlBase.$dirXml.$outputFileNameHead.".csv \n";
	}
}

/*
	$myLogFile = fopen($dirLog."insert_db.log", "a");
	fwrite($myLogFile, $log);
	fclose($myLogFile);
	*/
	fclose($outputFile);
	

	echo "齐活！功德无量！all done! \n";

?>
</body>
</html>
