<?PHP
require_once "../pcdl/html_head.php";
?>
<body>

<?php
	require_once("../pcdl/head_bar.php");
?>
<script src="../public/js/marked.js"></script>
<style>
		body{
			margin: unset;
		}
		.index_toolbar{
			position:unset;
		}

		.search_toolbar{
			height: initial;
			padding: 0.6em 1em 0.1em 1em;
			background-color: var(--tool-bg-color1);
			border-bottom: none;
		}
		.search_fixed{
			position: fixed;
			top:-500px;
			width:100%;
			display:flex;
			padding: 0.5em 1em;
        }
        #dict_search_result{
            display:flex;
        }
        #dict_list{
            flex:2;
            text-align: right;
            padding-right: 1em;
            border-right: 1px solid var(--border-line-color);
        }
        #dict_ref{
            flex:6;
            padding:0.5em 1.5em;
        }
        #dict_user{
            flex:2;
        }
		.dict_word_card{
		border-bottom: 1px solid var(--border-line-color);
    	padding: 5px 0;			
		display: block;
		border-radius: unset;
		margin: 10px 0;
		transition: unset;
		box-shadow: unset;
		}
		.dict_word{
		border-bottom: 1px solid var(--border-line-color);
    	padding: 5px 0;			
		display: block;
		border-radius: unset;
		margin: 10px 0;
		transition: unset;
		box-shadow: unset;
		}
		.dict_word>.dict {
			font-size: 110%;
			color: var(--main-color);
			border-bottom: unset;
			padding-bottom:10px;
		}
		.dict_word>.mean {
			font-size: unset;
			margin: 2px 0;
			line-height: 150%;
			font-weight: unset;
			display: block;
		}

		/*for word split part */
		.dropdown_ctl{
            display:inline-block;
			margin-right: 0.7em;
        }
        .dropdown_ctl>.content{
            display:flex;
			border: 1px solid var(--border-line-color);
    		border-radius: 99px;
        }
        .dropdown_ctl>.menu{
            position:absolute;
			box-shadow: 0 0 10px rgba(0,0,0,0.15);
            display:none;
        }
        .dropdown_ctl>.menu{
		background-color: white;
        }
        .dropdown_ctl>.content>.main_view>part{
            margin:0 0.5em;
            color:cornflowerblue;
            cursor: pointer;
        }
		.dropdown_ctl>.menu>.part_list {
			padding: 5px;
			cursor: pointer;
		}
		.dropdown_ctl>.menu>.part_list:hover {
			background-color:azure;
		}
		.dropdown_ctl>.content>.more_button {
			background-color: var(--btn-color);
			min-width: 1.4em;
			text-align: center;
			border-radius: 99px;
			cursor: pointer;
		}

	</style>
	<!-- tool bar begin-->
	<div id='search_toolbar' class="search_toolbar">
			<div style="display:flex;justify-content: space-between;">
				<div style="flex:2;">
					<div style="width: fit-content; margin-right: 0; margin-left: auto; margin-top: 1em; margin-bottom: 1em;">
						<guide gid="dict_search_input"></guide>
					</div>
				</div>
				<div style="flex:6;">
					<div>
						<div>
							<input id="dict_ref_search_input" type="input" placeholder="<?php echo $_local->gui->search;?>" onkeyup="dict_input_keyup(event,this)" style="    margin-left: 0.5em;width: 40em;max-width: 100%;font-size:140%;padding: 0.6em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onfocus="dict_input_onfocus()" />
						</div>
						<div id="word_parts">
							<div id="input_parts" style="font-size: 1.1em;padding: 2px 1em;"></div>
						</div>
					</div>
					
					<div id="pre_search_result" style="background-color: var(--btn-color);z-index: 50;">
						<div id="pre_search_word"  class="pre_serach_block">
							<div id="pre_search_word_title"   class="pre_serach_block_title">
								<div id="pre_search_word_title_left">单词</div>
								<div id="pre_search_word_title_right"></div>						
							</div>
							<div id="pre_search_word_content"   class="pre_serach_content">
							</div>
						</div>
					</div>
                </div>
				<span style="flex:2;">
				<button onclick="trubo_split()" style="height: fit-content;border: 1px solid var(--btn-border-color);background: var(--btn-color);border-radius: 4px;font-size: 1.2em;padding: 0.5em;">
				<?php echo $_local->gui->turbo_split;//强力拆分?>
				</button>
				<guide gid="comp_split">
				</guide>
				</span>
                <div ></div>
			</div>
			<div style="display:block;z-index: 5;text-align:center;">
            
			</div>
	</div>	
	<!--tool bar end -->

	<!-- tool bar fixed begin-->
	<div id='search_toolbar_1' class="search_toolbar search_fixed">
			<div style="display:flex;">
				<span >
				字典
				</span>
				<div>
					<div>
						<input id="dict_ref_search_input_1" type="input" placeholder="<?php echo $_local->gui->search;?>" onkeyup="dict_input_keyup(event,this)" style="margin-left: 0.5em;width: 40em;max-width: 80%;font-size:140%;padding: 0.3em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onfocus="dict_input_onfocus()">
					</div>
					<div id="pre_search_result_1" style="position: absolute;max-width: 100%; width: 50em;background-color: var(--btn-color);z-index: 51;display: none;">
						<div  class="pre_serach_block">
							<div class="pre_serach_block_title">
								<div id="pre_search_word_title_left_1">单词</div>
								<div id="pre_search_word_title_right_1"></div>						
							</div>
							<div id="pre_search_word_content_1"   class="pre_serach_content">
							</div>
						</div>
					</div>
				</div>
			</div>
			<div style="display:block;z-index: 5;">
			</div>
	</div>	
	<!--tool bar fixed end -->

	<script>
	 window.addEventListener('scroll',winScroll);
	function winScroll(e){ 
		if(GetPageScroll().y>150){
			$("#search_toolbar_1").css("top",0) ;
		}
		else{
			$("#search_toolbar_1").css("top",GetPageScroll().y - 150) ;
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
    <style>
        #dt_title{
            border-bottom: 2px solid var(--link-hover-color);
        }
    </style>
	<script language="javascript" src="dict.js"></script>

	<div id="dict_search_result" style="background-color:white;color:black;">
	</div>
	
    <?php
    if(!empty($_GET["key"])){
        echo "<script>";
        echo "dict_pre_word_click(\"{$_GET["key"]}\")";
        echo "</script>";
    }
	?>
	

<?php
include "../pcdl/html_foot.php";
?>

