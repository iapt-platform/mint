<?php
include "./config.php";
$workDir = $dir_mydocument;
$wbwFileName = $workDir.$_GET["filename"];

$outXml = "<wordlist>";
echo $outXml;

$rowCount=0;
$xmlObj = simplexml_load_file($wbwFileName);
		//get word list from pcs documnt
$dataBlock = $xmlObj->xpath('//block');
foreach($dataBlock as $block){
	if($block->info->type=="wbw"){
		//$words=$block->data->xpath('word');
		foreach($block->data->children() as $ws){
			$id=0;
			$pali=$ws->real;
			$mean=$ws->mean;
			$case=$ws->case;
			$factors=$ws->org;
			$fm="";
			if(isset($ws->om)){
				$fm=$ws->om;
			}
			$parent="";
			if(isset($ws->parent)){
				$parent=$ws->parent;
			}
			$note="";
			if(isset($ws->note)){
				$note=$ws->note;
			}
			$arrCase=explode("#",$case);
			$type=$arrCase[0];
			$gramma="";
			if(isset($arrCase[1])){
				$gramma=$arrCase[1];
			}
			if(!empty($pali) && ($mean!="?" || $factors!="?"  || $case!="?")){
				$outXml = "<word>";				
				$outXml = $outXml."<id>$id</id>";
				$outXml = $outXml."<pali>$pali</pali>";
				$outXml = $outXml."<mean>$mean</mean>";
				$outXml = $outXml."<type>$type</type>";
				$outXml = $outXml."<gramma>$gramma</gramma>";
				$outXml = $outXml."<parent>$parent</parent>";
				$outXml = $outXml."<factors>$factors</factors>";
				$outXml = $outXml."<factorMean>$fm</factorMean>";
				$outXml = $outXml."<note>$note</note>";
				$outXml = $outXml."<confer></confer>";
				$outXml = $outXml."<status>0</status>";
				$outXml = $outXml."<lock>FALSE</lock>";
				$outXml = $outXml."<dictname>$wbwFileName</dictname>";
				$outXml = $outXml."<dictType>wbw</dictType>";
				$outXml = $outXml."<fileName>$wbwFileName</fileName>";
				$outXml = $outXml."<parentLevel>0</parentLevel>";
				$outXml = $outXml."</word>";
				echo $outXml;
			}
			$rowCount++;
		}
	}
}

$outXml = "</wordlist>";
echo $outXml;

?>