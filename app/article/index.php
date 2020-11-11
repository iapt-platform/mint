<?php
require_once "../public/load_lang.php";
require_once "../path.php";
require_once "../pcdl/html_head.php";
?>
<body style="margin: 0;padding: 0;" class="reader_body" >

	<script src="../channal/channal.js"></script>
	<script src="./article.js"></script>

	<script src="../widget/click_dropdown.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/click_dropdown.css"/>

	<script>
	<?php
	$_id = "";
	$_display = "";
	$_channal  = "";
	$_collect = "";

	if(isset($_GET["id"])){
		echo "_articel_id='".$_GET["id"]."';";
	}
	if(isset($_GET["collect"])){
		echo "_collect_id='".$_GET["collect"]."';";
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

	#head_bar{
		display: flex;
    justify-content: space-between;
    height: 5em;
    background-color: var(--tool-bg-color1);
    border-bottom: 1px solid var(--tool-line-color);
	padding:10px;
	}

	.main_view{
		padding: 0 1em;
		max-width: 1280px;
		margin-left: auto;
		margin-right: auto;
	}

	.fun_frame {
		border-bottom: 1px solid gray;
		margin-right: 10px;
		margin-bottom: 10px;
	}
	.fun_frame .title{
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
	.active{
		background-color: var(--btn-hover-bg-color);
	}
	.icon_btn a {
	color: var(--main-color);
	}
	.icon_btn:hover a {
		color: var(--btn-hover-color);
	}
	.tran  img{
	max-width: 100%;
     max-height: 200px;
     width: auto;
     height: auto;
     object-fit: cover;
     background-size: contain;   
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
		echo "<button class='icon_btn'  title='{$_local->gui->modify} {$_local->gui->composition_structure}'>";
		echo "<a href='../article/my_article_edit.php?id=".$_GET["id"];
		echo "'>{$_local->gui->modify}</a></button>";
		
		if(isset($_GET["display"]) && $_GET["display"]=="para"){
			echo "<button class='icon_btn active' title='{$_local->gui->show} {$_local->gui->each_paragraph}'>";
			echo $_local->gui->each_paragraph;
			echo "</button>";
		}
		else{
			echo "<button class='icon_btn'>";
			echo "<a href='../article/?id=".$_GET["id"];
			if(isset($_GET["channal"])){
				echo "&channal=".$_GET["channal"];
			}
			echo "&display=para'  title='{$_local->gui->show} {$_local->gui->each_paragraph}'>";		
			echo $_local->gui->each_paragraph;
			echo "</a>";
			echo "</button>";
		}

		if(isset($_GET["display"]) && $_GET["display"]=="sent"){
			echo "<button class='icon_btn active'  title='{$_local->gui->show} {$_local->gui->each_sentence}'>";
			echo $_local->gui->each_sentence;
			echo "</button>";
		}
		else{
			echo "<button class='icon_btn'><a href='../article/?id=".$_GET["id"];
			if(isset($_GET["channal"])){
				echo "&channal=".$_GET["channal"];
			}
			echo "&display=sent";
			echo "'  title='{$_local->gui->show} {$_local->gui->each_sentence}'>{$_local->gui->each_sentence}</a></button>";
		}

		?>
			<button class='icon_btn'><a href="#"><?php echo $_local->gui->help; ?></a></button>
		</span>
	</div>
</div>
<div id="main_view" class="main_view">
<div id="article_head" style="border-bottom: 1px solid gray;">
	<div id="article_title" class="term_word_head_pali"><?php echo $_local->gui->title; ?></div>
	<div id="article_subtitle"><?php echo $_local->gui->sub_title; ?></div>
	<div id="article_author"><?php echo $_local->gui->author; ?></div>
</div>
<div id="contents_view">
	<div id="contents_div" style="padding: 0 1em 0 30px;">
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
			<div style="display:flex;justify-content: space-between;">
				<div class="title"><?php echo "Contributor" ?></div>
				<div class="click_dropdown_div">
					<div class="click_dropdown_button"><?php echo $_local->gui->more; ?></div>
					<div class="click_dropdown_content">
						<div class="click_dropdown_content_inner" id="channal_select">
							<?php echo $_local->gui->channels; ?>
						</div>
						<div>
						<button class="icon_btn click_dropdown_ok"><?php echo $_local->gui->confirm; ?></button>
						<button class="icon_btn click_dropdown_cancel"><?php echo $_local->gui->cancel; ?></button>
						</div>
					</div>	
				</div>
			</div>
			<div id="channal_list" class="content" style="max-height:20em;">
			</div>
		</div>
	</div>
</div>
</div>



<script>
	$(document).ready(function(){
	ntf_init();				
	click_dropdown_init();
	note_create();
	historay_init();
	if(_collect_id==""){
		articel_load(_articel_id);
		articel_load_collect(_articel_id);
	}
	else{
		collect_load(_collect_id);
	}
	});



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