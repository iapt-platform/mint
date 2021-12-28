<?php
//get book name

$filelist=array();
$fileNums=0;
$log="";
$outputFileName="bookname.csv";
$fp=fopen($outputFileName, "w");

function getChildNodeValue($array,$attName){
	if($array){
		foreach($array as $x=>$x_value) {
		  if($x==$attName){
			return $x_value;
			}
		}
	}
	return false;
}

if(($handle=fopen("filelist.csv",'r'))!==FALSE){
	while(($htmlFileName=fgetcsv($handle,0,','))!==FALSE){
		$FileName=$htmlFileName[1].".htm";
		$fileId=$htmlFileName[0];

		$dirLog=_DIR_LOG_."/";
		$dirHtml="pali/";
		$inputFileName=$dirHtml.$FileName;


		if(file_exists($inputFileName)==false){
			die('file ".."not exists...');
		}
	
		$xmlfile = $inputFileName;
		$xmlparser = xml_parser_create();
		echo "doing:".$xmlfile."<br>";

		// 打开文件并读取数据
		$fp = fopen($xmlfile, 'r');
		$xmldata = fread($fp,filesize($xmlfile));
		xml_parse_into_struct($xmlparser,$xmldata,$values);
		xml_parser_free($xmlparser);
		$begin = false;
		$suttaCount=0;
		$output="";
		$suttaName="";
		$log=$log."file:".$xmlfile."\r\n";
		$currNikaya="";
		$currBook="";
		foreach ($values as $child)
		{  
			$attributes=getChildNodeValue($child,"attributes");
			switch ($child["tag"])
			{
			case "BODY":
				break;
			case "P":
				$class=getChildNodeValue($attributes,"CLASS");
				if($class=="nikaya"){
					$currNikaya=$child["value"];
				}
				if($class=="book"){
					$currBook=$child["value"];
				}
				$csvWord[0]=$htmlFileName[0];
				$csvWord[1]=$htmlFileName[1];
				$csvWord[2]=$currNikaya;
				$csvWord[3]=$currBook;
				fputcsv($fp,$csvWord);
				echo $currBook."<br>";
				break;
			}
		}
		
		$fileNums++;
	}
	
}
fclose($fp);

?>