<?php
require_once "../public/_pdo.php";
require_once "../path.php";
/*
if(isset($_GET["word"])){
	if(empty($_GET["word"])){
		return;
	}
	$word=mb_strtolower($_GET["word"],'UTF-8');
}
*/
if(isset($_GET["id"])){
	$_get_id=$_GET["id"];
}
if(isset($_GET["word"])){
	$_get_word=$_GET["word"];
}
if(isset($_GET["author"])){
	$_get_author=$_GET["author"];
}
else{
	$_get_author="";
}
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="../studio/css/style.css"/>
        <link type="text/css" rel="stylesheet" href="../pcdl/css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="../pcdl/css/style_mobile.css" media="screen and (max-width:767px)">
	
	<title id="doc_title">圣典百科</title>
	<style>

	</style>
	<script src="../public/js/jquery.js"></script>
	<script src="../public/js/comm.js"></script>
	<script src="../term/term.js"></script>
	<script src="../term/note.js"></script>
	<script src="wiki.js"></script>
	
	<style>
	.term_link,.term_link_new{
		color: blue;
		padding-left: 2px;
		padding-right: 2px;
	}
	.term_link_new{
		color:red;
	}
	#search_result{
		position: absolute;
		background: wheat;
		max-width: 95%;
		width: 24em;
	}
	chapter{
		color: blue;
		text-decoration: none;
		cursor: pointer;
	}
	chapter:hover{
		color: blue;
		text-decoration: underline;
	}
	.icon{
		width: 15px;
		height: 15px;
	}
	.submenu_title{
		font-size: 120%;
		font-weight: 700;		
	}
	.term_word_head_pali {
		text-transform: capitalize;
		font-size: 200%;
		margin: 0.5em 0;
	}
	.term_word_head{
		border-bottom: 1px solid #cecece;
		padding: 5px 0;
	}
	.term_block{
		border-bottom: 1px solid #cecece;
		padding: 5px 0;
	}
	.term_word_head_authors a{
		color: blue;
		margin: 0 3px;
	}
	.term_word_head_authors a:hover{
		text-decoration: underline;
		cursor: pointer;
	}
	note{
		display: block;
		background-color: #80808029;
		padding: 0.5em;
	}
	note .ref{
		text-align: right;
		padding: 5px;
		font-size: 80%;
	}
	.term_block_bar {
		display: flex;
		justify-content: space-between;
	}
	#head_bar{
		display: flex;
    justify-content: space-between;
    height: 5em;
    background-color: var(--bookx);
    border-bottom: 1px solid var(--tool-line-color);
	}
	.term_block_bar_left{
		display: flex;
	}
	.term_block_bar_left_icon{
    display: inline-block;
    width: 1.5em;
    text-align: center;
    height: 1.5em;
    background-color: gray;
    font-size: 180%;
    color: white;
    border-radius: 99px;
	}
	.term_block_bar_left_info{
		    padding-left: 8px;
	}
	.term_meaning{
		font-weight: 700;
	}
	.term_author{
		font-size: 80%;
		color: gray;
	}
	.term_tag{
		font-size: 80%;
		font-weight: 500;
		margin: 0 8px;
	}
	.term_link {
    cursor: pointer;
	}
}

	</style>
<body style="margin: 0;padding: 0;" class="reader_body" onload="<?php
if(isset($_get_id)){
echo "wiki_load_id('{$_get_id}')";
}
else if(isset($_get_word)){
echo "wiki_load_word('{$_get_word}')";
}
?>">
<script>
term_word_link_fun("wiki_goto_word");
</script>
<style>
	.index_toolbar{
		position:unset;
	}
	#pali_pedia{
		font-size: 200%;
    margin-top: auto;
    margin-bottom: auto;
    padding-left: 0.5em;
	}
</style>

<?php
    require_once("../pcdl/head_bar.php");
?>
<div id="head_bar" >
	<div id="pali_pedia">圣典百科</div>


	<div>
		<span id="wiki_search" style="width:20em;">
			<span>
				<input id="wiki_search_input" type="input" placeholder="search" style="width:30em;" onkeyup="wiki_search_keyup(event,this)"/>
			</span>
			<span id="search_result">
			</span>
		</span>	
		<span>
			<a href="#">[设置]</a>
			<a href="#">[建立词条]</a>
			<a href="#">[帮助]</a>
			<a href="#">[当前用户]</a>
		</span>

	
	</div>
</div>

<div id="wiki_contents">
loading...
</div>

<button onclick="run()">run</button>
<button onclick="run2()">run2</button>
</body>
</html>