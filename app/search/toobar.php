<style>
		body{
			margin: unset;
		}
		.index_toolbar{
			position:unset;
		}

		.search_toolbar{
			
			height: initial;
			padding: 0.7em 1em 0 1em;;
			background-color: var(--tool-bg-color1);
			border-bottom: none;
			color: var(--tool-color1);
		}
		.search_fixed{
			position: fixed;
			top:-500px;
			width:100%;
			display:flex;
			padding: 0.5em 1em;
		}
	</style>
	<!-- tool bar begin-->
	<div id='search_toolbar' class="search_toolbar">
			<div style="display:flex;">
				<span >
					搜索
				</span>
				<div>
					<div>
						<input id="dict_ref_search_input" type="input" placeholder="<?php echo $_local->gui->search;?>" onkeyup="search_input_keyup(event,this)" style="    margin-left: 0.5em;width: 40em;max-width: 80%;font-size:140%;padding: 0.6em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onfocus="search_input_onfocus()">
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
			</div>
			<div style="display:block;z-index: 5;">
				<ul id="dict_type" class="lab_tab" style="color:black;">
				<?php
				if(isset($_GET["key"])){
					$key = "?key=".$_GET["key"];
				}
				else{
					$key = "";
				}
				?>
					<li id="dt_all" ><a href="../search/index.php<?php echo $key;?>"><span >全部</span><span id="search_all_num"></span></a></li>
					<li id="dt_title" ><a href="../search/title.php<?php echo $key;?>"><span >标题</span><span id="search_title_num"></span></a></li>
					<li id="dt_pali" ><a href="../search/paliword.php<?php echo $key;?>"><span >巴利原文</span><span id="search_palitext_num"></span></a></li>
					<li id="dt_bold" ><a href="../search/bold.php<?php echo $key;?>"><span ><?php echo $_local->gui->vannana;?></span><span id="search_bold_num"></span></a></li>
					<li id="dt_trans" ><a href="../search/trans.php<?php echo $key;?>"><span ><?php echo $_local->gui->translate;?></span><span id="search_trans_num"></span></a></li>
				</ul>
			</div>
	</div>	
	<!--tool bar end -->

	<!-- tool bar fixed begin-->
	<div id='search_toolbar_1' class="search_toolbar search_fixed">
			<div style="display:flex;">
				<span >
					搜索
				</span>
				<div>
					<div>
						<input id="dict_ref_search_input_1" type="input" placeholder="<?php echo $_local->gui->search;?>" onkeyup="search_input_keyup(event,this)" style="margin-left: 0.5em;width: 40em;max-width: 80%;font-size:140%;padding: 0.3em;color: var(--btn-hover-bg-color);background-color: var(--btn-color);" onfocus="search_input_onfocus()">
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
				<ul id="dict_type" class="lab_tab" style="color:black;">
				<?php
				if(isset($_GET["key"])){
					$key = "?key=".$_GET["key"];
				}
				else{
					$key = "";
				}
				?>
					<li id="dt_all_1" ><a href="../search/index.php<?php echo $key;?>"><span >全部</span><span id="search_all_num_1"></span></a></li>
					<li id="dt_title_1" ><a href="../search/title.php<?php echo $key;?>"><span >标题</span><span id="search_title_num_1"></span></a></li>
					<li id="dt_pali_1" ><a href="../search/paliword.php<?php echo $key;?>"><span >巴利原文</span><span id="search_palitext_num_1"></span></a></li>
					<li id="dt_bold_1" ><a href="../search/bold.php<?php echo $key;?>"><span ><?php echo $_local->gui->vannana;?></span><span id="search_bold_num_1"></span></a></li>
					<li id="dt_trans_1" ><a href="../search/trans.php<?php echo $key;?>"><span ><?php echo $_local->gui->translate;?></span><span id="search_trans_num_1"></span></a></li>
				</ul>
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