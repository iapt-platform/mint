<?php
require_once "../public/load_lang.php";
require_once "../path.php";
require_once "../pcdl/html_head.php";
?>
<body style="margin: 0;padding: 0;" class="reader_body" >
	<script src="../term/term.js"></script>
	<script src="../term/note.js"></script>
	<script src="../channal/channal.js"></script>
	<script src="./article.js"></script>
	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term.css"/>
	<link type="text/css" rel="stylesheet" href="../public/js/jquery-ui-1.12.1/jquery-ui.css"/>
	<script>
	<?php
	$_id = "";
	$_display = "";
	$_channal  = "";

	if(isset($_GET["id"])){
		echo "_articel_id='".$_GET["id"]."';";
	}
	if(isset($_GET["display"])){
		echo "_display='".$_GET["display"]."';";
	}
	if(isset($_GET["channal"])){
		echo "_channal='".$_GET["channal"]."';";
	}
	if(isset($_GET["lang"])){
		echo "_lang='".$_GET["lang"]."';";
	}
	if(isset($_GET["author"])){
		echo "_author='".$_GET["author"]."';";
	}
	?>
	</script>
	<style>


	#search_result{
		position: absolute;
		background: wheat;
		max-width: 95%;
		width: 24em;
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

	note .ref{
		text-align: right;
		padding: 5px;
		font-size: 75%;
		margin-top: 8px;
	}
	note{
		padding: 0.5em 0.8em;
		margin-bottom: 0.4em;
		border-radius: 5px;
		line-height:1.3em;
		<?php
		if(isset($_GET["display"]) && $_GET["display"]=="para"){
			echo "display:inline;";
		}
		else{
			echo "display:block;";
			echo "background-color: #80808014;";
		}
		?>
	}
	note>.tran{
		color: #5c5c5c;
		padding-left: 1em;
	}
	note>.palitext , .palitext{
		font-family: Noto serif;
		line-height: 1.5em;
		color: #9f3a01;
		font-weight: 500;
	}
	note>.palitext>note{
		display:inline;
		color:blue;
		background-color: unset;
		padding: unset;
		margin-bottom: unset;
		border-radius: unset;
	}


	.term_block_bar {
		display: flex;
		justify-content: space-between;
	}
	#head_bar{
		display: flex;
    justify-content: space-between;
    height: 5em;
    background-color: var(--tool-bg-color1);
    border-bottom: 1px solid var(--tool-line-color);
	padding:10px;
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

	.main_view{
		padding: 0 1em;
		max-width: 1280px;
		margin-left: auto;
		margin-right: auto;
	}
	#term_list_right{
		width: 25em;
	}
	#term_list{
		width: 100%;
		padding: 0.5em;
	}
	#term_list_div{
		display: flex;
		justify-content: space-between;
	}
	.fun_frame {
		border-bottom: 1px solid gray;
		margin-right: 10px;
		margin-bottom: 10px;
	}
	.fun_frame>.title{
		padding:6px;
		font-weight: 700;
	}
	.fun_frame>.content{
		padding:6px;
		max-height:6em;
		overflow-y: scroll;
	}
	
	.fixed{
		position:fixed;
		right: 0;
    	top: 0;
	}
	.when_right_fixed{
		padding-right:20em;
	}
	<?php
		if(isset($_GET["display"]) && $_GET["display"]=="para"){

		}
		else{
?>
	.bg_color_1{
		background-color:#ebebeb66;
	}
	.bg_color_2{
		background:linear-gradient(to right, #6afdb033, #ebebeb66);
	}
	.bg_color_3{
		background:linear-gradient(to right, #6a95fd26, #ebebeb66);
	}
	.bg_color_4{
		background:linear-gradient(to right, #f9e7911c, #ebebeb66);
	}
	.bg_color_5{
		background:linear-gradient(to right, #fe99b91c, #ebebeb66);
	}
<?php
		}
		?>


	pre {
		white-space: pre-line;
		font-family: auto;
		border-left: 3px solid var(--border-shadow);
		margin-left: 1em;
		padding-left: 0.5em;
	}
	#contents_view{
		display:flex;
	}
	#contents_div{
		flex:7;
	}
	#contents{
		min-height: 400px;
	}
	#contents li{
		white-space: normal;
	}
	#right_pannal{
		flex:3;
		max-width:20em;
	}
	#head_bar{
		height:unset;
	}

<?php
		if(isset($_GET["display"]) && $_GET["display"]=="para"){
?>
	.tran>p{
		display:inline;
	}
	note{
		padding: 2px;
		margin-bottom: unset;
	}
<?php
		}
?>

#toc_content .level_2{
	padding-left:0.5em;
}
#toc_content .level_3{
	padding-left:1em;
}
#toc_content .level_4{
	padding-left:1.5em;
}
#toc_content .level_5{
	padding-left:2em;
}
.ui-dialog-titlebar{
		display: flex;
    justify-content: space-between;
	background-color: var(--btn-bg-color);
    padding: 5px;
	}
	.ui-widget-content{
		background-color: var(--bg-color);
	}
	.ui-dialog{
		box-shadow:  8px 8px 20px var(--border-shadow);
	}
	</style>

<style media="screen and (max-width:767px)">
#right_pannal{
	display:none;
}
.when_right_fixed{
	padding-right:0;
}
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

<script>
term_word_link_fun("wiki_goto_word");
</script>

<?php
    require_once("../pcdl/head_bar.php");
?>
<div id="head_bar" >
	<div id="pali_pedia" style="display:flex;">
		<span><?php echo $_local->gui->anthology; ?></span>
	</div>

	<div>
		<span>
		<?php
		echo "<button class='icon_btn'><a href='../article/my_article_edit.php?id=".$_GET["id"];
		echo "'>{$_local->gui->edit_now}</a></button>";
		echo "<button class='icon_btn'><a href='../article/?id=".$_GET["id"];
		echo "&display=para";
		echo "'>{$_local->gui->each_paragraph}</a></button>";
		echo "<button class='icon_btn'><a href='../article/?id=".$_GET["id"];
		echo "&display=sent";
		echo "'>{$_local->gui->each_sentence}</a></button>";
		?>
			<button class='icon_btn'><a href="#"><?php echo $_local->gui->help; ?></a></button>
		</span>
	</div>
</div>
<div id="main_view" class="main_view">
<div id="article_head" style="border-bottom: 1px solid gray;">
	<div id="article_title" class="term_word_head_pali">Title</div>
	<div id="article_subtitle">Subtitle</div>
	<div id="article_author">author</div>
</div>
<div id="contents_view">
	<div id="contents_div" style="padding: 0 1em;">
		<div id="contents">
		<?php echo $_local->gui->loading; ?>...
		</div>
		<div id="contents_foot">
			<div id="contents_nav" style="display:flex;justify-content: space-between;">
				<div id="contents_nav_left"></div>
				<div id="contents_nav_right"></div>
			</div>
			<div id="contents_dicuse">
			
			</div>
		</div>
	</div>
	<div id="right_pannal">
		<div class="fun_frame">
			<div id = "collect_title" class="title">Table of Content</div>
			<div id = "toc_content" class="content" style="max-height:20em;">
			</div>
		</div>
		<div class="fun_frame">
			<div class="title"><?php echo $_local->gui->channels; ?></div>
			<div id="channal_list" class="content" style="max-height:20em;">
			</div>
		</div>
	</div>
</div>
</div>

<!-- ui-dialog -->
<div id="dialog" title="Dialog Title">
	<div id="edit_dialog_content"></div>
</div>

<script>
	note_create();
	articel_load(_articel_id);
	articel_load_collect(_articel_id);

	 window.addEventListener('scroll',winScroll);
	function winScroll(e){ 
		if(GetPageScroll().y>220){

		}
		else{

		}
		
	}
 //滚动条位置
function GetPageScroll() 
{ 
	var pos=new Object();
	var x, y; 
	if(window.pageYOffset) 
	{	// all except IE	
		y = window.pageYOffset;	
		x = window.pageXOffset; 
	} else if(document.documentElement && document.documentElement.scrollTop) 
	{	// IE 6 Strict	
		y = document.documentElement.scrollTop;	
		x = document.documentElement.scrollLeft; 
	} else if(document.body) {	// all other IE	
		y = document.body.scrollTop;	
		x = document.body.scrollLeft;   
	} 
	pos.x=x;
	pos.y=y;
	return(pos);
}
	</script>


</body>
</html>