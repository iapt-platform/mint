<?php 
$input = file_get_contents("php://input"); //

$strHead = mb_strstr($input,'#',true,"UTF-8");
$xmlBody = strstr($input,'#');
$xmlBody = substr($xmlBody,1);
parse_str($strHead);//

$csvPath="../user/My Document/";

$datatime=date("Ymd").date("His");
$purefilename = basename($filename);
$csvFilename = $csvPath.$purefilename.'_'.$datatime.".csv";

//save histroy file
$myfile = fopen($csvFilename, "w") or die("Unable to open file!");
$list=array("pali","real","id","mean","org","org_mean","gramma","bookmark_color","bookmark_text","note","lock");
fputcsv($myfile,$list);
	
$xml = simplexml_load_string($xmlBody);
		//get word list from xml documnt
$wordsSutta = $xml->xpath('//word');

		/*prepare words from xmldoc and remove same words*/
foreach($wordsSutta as $ws){
	$pali = $ws->pali;
	$real = $ws->real;
	$id = $ws->id;
	$mean = $ws->mean;
	$org = $ws->org;
	$om = $ws->om;
	$gramma = $ws->case;
	$bmc = $ws->bmc;
	$bmt = $ws->bmt;
	$note = $ws->note;
	$lock = $ws->lock;
	$list=array($pali,$real,$id,$mean,$org,$om,$gramma,$bmc,$bmt,$note,$lock);
	fputcsv($myfile,$list);
}

fclose($myfile);


echo("Successful");
echo "<a href=\"\">download</a>"
?>

