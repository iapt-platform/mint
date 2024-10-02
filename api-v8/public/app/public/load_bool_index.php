<?php
require_once '../public/config.php';

/*
load language file
范例
echo _local->gui->welcome;
*/
function _load_book_index(){
	global $_book_index,$_dir_lang,$currLanguage;
	if(file_exists($_dir_lang.$currLanguage.".json")){
		$_book_index=json_decode(file_get_contents($_dir_book_index."a/".$currLanguage.".json"));
	}
	else{
		$_book_index=json_decode(file_get_contents($_dir_book_index."a/default.json"));
	}
}

function _get_book_info($index){
	global $_book_index;
	foreach($_book_index as $book){
		if($book->row==$index){
			return($book);
		}
	}
	return(null);
}

function _get_book_path($index){
	global $_book_index;
}
?>