<?php
require_once "../public/load_lang.php";
require_once "../path.php";
require_once "../pcdl/html_head.php";
?>
<body style="margin: 0;padding: 0;" class="reader_body" >


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
		echo "_collection_id='".$_GET["collect"]."';";
	}
	if(isset($_GET["collection"])){
		echo "_collection_id='".$_GET["collection"]."';";
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
	if(isset($_GET["mode"]) && $_GET["mode"]=="edit" && isset($_COOKIE["userid"])){
		#登录状态下 编辑模式
		$_mode = "edit";
		echo "_mode='edit';";
	}
	else{
		$_mode = "read";
		echo "_mode='read';";
	}
	if(isset($_GET["display"])){
		if($_mode == "edit"){
			$_display = "sent";
			echo "_display='sent';";	
		}
		else{
			$_display = $_GET["display"];
			echo "_display='".$_GET["display"]."';";	
		}
	}
	else{
		if($_mode=="read"){
			$_display = "para";
			echo "_display='para';";
		}
		else{
			$_display = "sent";
			echo "_display='sent';";			
		}

	}	
	if(isset($_GET["direction"])){
		$_direction = $_GET["direction"];
		echo "_direction='".$_GET["direction"]."';";
	}
	else{
		if($_mode=="read"){
			$_direction = "row";
			echo "_direction='row';";
		}
		else{
			$_direction = "col";
			echo "_direction='col';";
		}
	}
	$contentClass= "";
	if($_direction=="row"){
		$contentClass .= ' horizontal ';
	}
	else{
		$contentClass .= ' vertical ';
	}
	if($_display=="para"){
		$contentClass .= ' para_mode ';
	}
	else{
		$contentClass .= ' sent_mode ';
	}
	$contentClass .= " $_mode ";
	
	?>
	</script>


<link type="text/css" rel="stylesheet" href="style.css"  />
<link type="text/css" rel="stylesheet" href="pad.css" media="screen and (max-width:1280px)" />
<link type="text/css" rel="stylesheet" href="mobile.css" media="screen and (max-width:800px)" />
<link type="text/css" rel="stylesheet" href="print.css" media="print" />

<link href="../../node_modules/jquery.fancytree/dist/skin-win7/ui.fancytree.css" rel="stylesheet" type="text/css" class="skinswitcher">
<script src="../tree/jquery.fancytree.js" type="text/javascript"></script>
<script src="../article/my_collect.js" type="text/javascript"></script>

<style>
#toc_content ul.fancytree-container{
	border:unset;
}
.fancytree-container .active {
    font-weight: 700;
    color: var(--main-color);
	background: linear-gradient(to right, var(--link-color), var(--nocolor));
    border-radius: 5px;
}
span.fancytree-title{
	color: var(--main-color1);
}
span.fancytree-node{
	display: flex;
}
#toc_content{
	max-height: 25vw;
    width: max-content;
}
</style>

<?php
    require_once("../pcdl/head_bar.php");
?>
<div id="head_bar" >
	<div style="display:flex;">
	<div id="article_path" >
	</div>
	<div id="article_path_title"></div>
	</div>

	<div style="margin: auto 0;">
		<span id="head_span">
		<?php
		
		if(isset($_GET["id"])){
			echo "<button class='icon_btn'  title='{$_local->gui->modify} {$_local->gui->composition_structure}'>";
			echo "<a href='../article/my_article_edit.php?id=".$_GET["id"];
			echo "' target='_blank'>{$_local->gui->modify}</a></button>";
			
			echo "<button class='icon_btn'  title='{$_local->gui->add}{$_local->gui->subfield}'>";
			echo "<a href='../article/frame.php?id=".$_GET["id"];
			echo "'>{$_local->gui->add}{$_local->gui->subfield}</a></button>";	
			
		}

		?>
		<span>
		<?php include "../reader/right_tool_bar.php";?>
		</span>
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
	<div id="contents_div">
	
		<div id="contents" class="<?php echo $contentClass;?>">
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
		<div class="fun_frame" style="overflow-x: scroll;">
			<div id = "collect_title" class="title"><?php echo $_local->gui->contents; ?></div>
			<div id = "toc_content" class="content" >
			</div>
		</div>
		<div class="fun_frame">
			<div style="display:flex;justify-content: space-between;">
				<div class="title"><?php echo $_local->gui->contributor; ?></div>
				<div class="click_dropdown_div">
					<div class="channel_select_button" onclick="onChannelMultiSelectStart()"><?php echo $_local->gui->select; ?></div>
				</div>
			</div>
			<div class='channel_select'>
				<button onclick='onChannelChange()'><?php echo $_local->gui->confirm; ?></button>
				<button onclick='onChannelMultiSelectCancel()'><?php echo $_local->gui->cancel; ?></button>
			</div>
			<div id="channal_list" class="content" style="max-height:25vw;">
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
	if(_articel_id==""){
		collect_load(_collection_id);
	}
	else{
		articel_load(_articel_id,_collection_id);
		if(_collection_id!=""){
			articel_load_article_list(_articel_id,_collection_id);
		}
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

<div class="modal_win_bg">
</div>
<div id="model_win" class="model_win_container"></div>

</body>
</html>