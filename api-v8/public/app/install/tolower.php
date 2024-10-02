<?php
function makeRealWord($inString){
	$paliletter="āīūṅñṭḍṇḷṃṁŋabcdefghijklmnoprstuvy";
	$lowerWord=mb_strtolower($inString,'UTF-8');
	echo $lowerWord;
	$output="";
	for($i=0;$i<mb_strlen($lowerWord,"UTF-8");$i++){
		$oneLetter=mb_substr($lowerWord,$i,1,"UTF-8");
		echo "oneLetter: $oneLetter <br>";
		if(mb_strstr($paliletter,$oneLetter,'UTF-8')!==FALSE){
			$output.=$oneLetter;
		}
	}
	return($output);
}
echo makeRealWord("Ā3Ī-Ū4ṄÑsdṬddḌn#nṆll@ḶnṂmṀdŊ");
?>