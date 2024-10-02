<!DOCTYPE html>
<html>
	<head>
		<title>wikipal</title>
		<meta http-equiv="refresh" content="0,../article/index.php?<?php echo $_SERVER['QUERY_STRING'];?>"/>
	</head>
	
	<body>
		loading...
    </body>
</html>
<?php
exit;
?>

<?php
require_once "../public/load_lang.php";
require_once "../config.php";
require_once "../pcdl/html_head.php";
?>
<body style="margin: 0;padding: 0;" class="reader_body" >

</body>
</html>

	<script src="../channal/channal.js"></script>
	<script src="./reader.js"></script>
	<script src="../widget/click_dropdown.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/click_dropdown.css"/>
	<link type="text/css" rel="stylesheet" href="style.css"  />
	<link type="text/css" rel="stylesheet" href="mobile.css" media="screen and (max-width:800px)" />
	<link type="text/css" rel="stylesheet" href="print.css" media="print" />

	<script>
	<?php
	$_view = "";
	$_display = "";
	$_channal  = "";
	$_collect = "";

	if(isset($_GET["view"])){
		echo "_reader_view='".$_GET["view"]."';";
	}
	if(isset($_GET["id"])){
		echo "_reader_sent_id='".$_GET["id"]."';";
	}
	if(isset($_GET["book"])){
		echo "_reader_book='".$_GET["book"]."';";
	}
	if(isset($_GET["para"])){
		echo "_reader_para='".$_GET["para"]."';";
	}
	if(isset($_GET["par"])){
		#为了避免 &para被urlencode替换问题
		echo "_reader_para='".$_GET["par"]."';";
	}
	if(isset($_GET["begin"])){
		echo "_reader_begin='".$_GET["begin"]."';";
	}
	if(isset($_GET["end"])){
		echo "_reader_end='".$_GET["end"]."';";
	}
	
	if(isset($_GET["channal"])){
		echo "_channal='".$_GET["channal"]."';";
	}
	if(isset($_GET["channel"])){
		#纠正拼写错误
		echo "_channal='".$_GET["channel"]."';";
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
	?>
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
			<input type="checkbox" onchange="setVisibility('palitext_div',this)" checked><?php echo $_local->gui->script; ?>
	
			<span>
			<?php include "../reader/right_tool_bar.php";?>
			</span>
		</span>
	</div>
</div>


	<div id="main_view" class="main_view">
		<div id="article_head" style="border-bottom: 1px solid gray;">
			<div id="article_title" class="term_word_head_pali"><?php echo $_local->gui->title; ?></div>
			<div  id='path_div' style="display:flex;justify-content: space-between;">
				<div id="article_path">
				<span id="para_path"></span>
				<span class="case_dropdown" id="para_path_next_level">
				……
				<div id="toc_next_menu" class="case_dropdown-content">
				</div>
				</span>
				</div>
				<div id="article_lang">
					<div class="click_dropdown_div">
						<div class="click_dropdown_button">语言</div>
						<div class="click_dropdown_content">
							<div class="click_dropdown_content_inner">
								<a>简体中文</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<div id="contents_view">
			<div id="contents_div" >
			<div id="contents" class="
				<?php
				if($_direction=="row"){
					echo ' horizontal ';
				}
				else{
					echo ' vertical ';
				}
				if($_display=="para"){
					echo ' para_mode ';
				}
				else{
					echo ' sent_mode ';
				}
				?>
		">
				<?php echo $_local->gui->loading; ?>...</div>
				<div id="contents_toc"></div>
				<div id="contents_foot">
					<div id="contents_nav" style="display:flex;justify-content: space-between;">
						<div id="contents_nav_left"></div>
						<div id="contents_nav_right"></div>
					</div>
					<div id="contents_dicuse"></div>
				</div>
			</div>
			<div id="right_pannal">
				<div class="fun_frame" style="overflow-x: scroll;position: fixed;width: 18%;">
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
	reader_load();
	historay_init();
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