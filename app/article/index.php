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
		echo "_collect_id='".$_GET["collect"]."';";
	}
	if(isset($_GET["collection"])){
		echo "_collection_id='".$_GET["collection"]."';";
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
	if(isset($_GET["mode"]) && $_GET["mode"]=="edit"){
		$_mode = "edit";
		echo "_mode='edit';";
	}
	else{
		$_mode = "read";
		echo "_mode='read';";
	}
	?>
	</script>


<link type="text/css" rel="stylesheet" href="style.css"  />
<link type="text/css" rel="stylesheet" href="mobile.css" media="screen and (max-width:800px)" />
<link type="text/css" rel="stylesheet" href="print.css" media="print" />


<?php
    require_once("../pcdl/head_bar.php");
?>
<div id="head_bar" >
	<div id="pali_pedia" style="display:flex;">
		<span><?php echo $_local->gui->anthology; ?></span>
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
		<div id="contents" 
		<?php
				if($_mode=="read"){
					echo 'class="para_mode horizontal"';
				}
				else{
					echo 'class="sent_mode vertical"';
				}
				?>
		>
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
			<div id = "collect_title" class="title"><?php echo $_local->gui->contents; ?></div>
			<div id = "toc_content" class="content" style="max-height:25vw;">
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
	if(_collect_id==""){
		articel_load(_articel_id,_collection_id);
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

<div class="modal_win_bg">
</div>
<div id="model_win" class="model_win_container"></div>

</body>
</html>