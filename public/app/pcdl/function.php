<?php
if(isset($_COOKIE["language"])){
	$lang=$_COOKIE["language"];
}
else{
	$lang="en";
}

if(file_exists("language/db_{$lang}.php")){
	include_once "language/db_{$lang}.php";
}
else{
	include_once "language/db_default.php";
}
function get_book_info_html($book_id){
	if(isset($GLOBALS['book_name'][$book_id])){
		$book=$GLOBALS['book_name'][$book_id];
		return("《".$book[0]."》<span>".$book[1]."</span>");
	}
}
?>