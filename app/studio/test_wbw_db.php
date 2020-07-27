<?php
//begin updata wbw database 
$xmlObj = simplexml_load_file("../user/My Document/8.pcs");
$dataBlock = $xmlObj->xpath('//block');
foreach($dataBlock as $block){
	//print_r($block);
	if($block->info->type=="wbw"){
		echo "<h3>book:".$block->info->book." par:".$block->info->paragraph."</h3>";
		$words=$block->data->xpath('word');
		foreach($words as $word){
			echo $word->real."-";
		}
	}
}
?>