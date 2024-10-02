<?php 
/*
export inline dictionary
*/
$input = file_get_contents("php://input"); //

$strHead = mb_strstr($input,'#',true,"UTF-8");
$xmlBody = strstr($input,'#');
$xmlBody = substr($xmlBody,1);
parse_str($strHead);//

$csvPath="../user/My Document/";

$purefilename = basename($filename);
$csvFilename = $csvPath.$purefilename.".ild";

//save histroy file
$myfile = fopen($csvFilename, "w") or die("Unable to open file!");
fwrite($myfile, $xmlBody);
/*
$list=array("recorderId","pali","mean","type","gramma","parent","factors","factorMean","note","confer","status","delete","dictname","dictType","fileName","parentLevel");
fputcsv($myfile,$list);
	
$xml = simplexml_load_string($xmlBody);
//get word list from xml documnt
$wordsSutta = $xml->xpath('//word');


foreach($wordsSutta as $ws){
	$list=array(
		$ws->recorderId,
		$ws->pali,
		$ws->mean,
		$ws->type,
		$ws->gramma,
		$ws->parent,
		$ws->factors,
		$ws->factorMean,
		$ws->note,
		$ws->confer,
		$ws->status,
		$ws->delete,
		$ws->dictname,
		$ws->dictType,
		$ws->fileName,
		$ws->parentLevel
		);
	fputcsv($myfile,$list);
}
*/

fclose($myfile);


echo("Successful");
echo "<a href=\"\">download</a>"
?>

