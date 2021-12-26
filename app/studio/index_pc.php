<?php
require_once '../studio/index_head.php';
?>

<body class="indexbody" onload="wizard_palicannon_init()">
<script >
	var gCurrPage="pali_canon";
</script>

<style>
	#pali_canon {
		background-color: var(--btn-border-color);
		
	}
	#pali_canon:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>

	<script language="javascript" src="module/editor_palicannon/palicannon.js"></script>
	<script language="javascript" src="module/editor_palicannon/language/<?php echo $currLanguage; ?>.js"></script>

	<script language="javascript" src="js/wizard.js"></script>
	<script language="javascript" src="js/editor.js"></script>
	<script language="javascript" src="js/xml.js"></script>

		<!-- tool bar begin-->
		<?php
		require_once '../studio/index_tool_bar.php';
		?>
		<!--tool bar end -->

	<div class="index_inner" style="width: 100%;">

<div class="editor_wizard">
	<div id="wizard_div"></div>
	<div class="editor_wizard_pali_cannon" style = "padding-left:16em;">
		<div class="pali_book_select_div_shell">
			<div class="pali_book_select_div">
				<div class="book_index_shell">
					<ul id="id_wizard_palicannon_index_c1" class="pali_book_select">
					</ul>
				</div>				
					
				<div class="book_index_shell">				
					<div id="id_wizard_palicannon_index_c2" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">				
					<div id="id_wizard_palicannon_index_c3" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">					
					<div id="id_wizard_palicannon_index_c4" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_index_book" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h1" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h2" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h3" class="pali_book_select">
					</div>
				</div>
				
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h4" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h5" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h6" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h7" class="pali_book_select">
					</div>
				</div>
				<div class="book_index_shell">
					<div id="id_wizard_palicannon_book_h8" class="pali_book_select">
					</div>
				</div>
				
				<div class="enter"></div>
			</div>
		</div>
		<div id='file_list'>
		</div>	
			<div id="wizard_palicannon_par_select">
				<a name="toc_root"></a>
				<div id="wizard_palicannon_par_select_toc" class="fixedsticky" style="flex:2;">
				
				</div>
				<div id="wizard_palicannon_par_select_text" style="flex:8;">
					<div id="wizard_palicannon_par_select_text_head">
						<h2 id="wizard_palicannon_par_select_text_head_bookname"><span  ><?php echo $module_gui_str['editor_wizard']['1012'];?></span></h2><span id="star_lv"></span>		
						<div class="wizard_palicannon_par_select_text_head_inner">
							<div id='para_res_list'></div>
							<div id="wizard_palicannon_par_select_text_head_res">
							</div>
						</div>
						
						<div id="wizard_palicannon_par_select_text_head_button">
							<form action="project.php" method="post" onsubmit="return pali_canon_edit_now(this)" target="_blank">
								<div style="display:none;">
									<input type="input" name="op" value="create" />
									<textarea id="project_new_res_data" rows="3" cols="18" name="data"></textarea><br>
								</div>
								<input type="submit" value=<?php  echo $_local->gui->edit_now?> />
							</form>
							<!--
							<button onclick="book_res_edit_now(1)">
								<svg class="icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_assignment_returned"></use></svg>
								<?php echo $module_gui_str['editor_wizard']['1013'];?>
							</button>
							
							<button  onclick="explorer_res_add_to_list()">
								<svg class="icon" viewBox="0 0 24 24" id="ic_add_shopping_cart_24px" ><path d="M11 9h2V6h3V4h-3V1h-2v3H8v2h3v3zm-4 9c-1.1 0-1.99.9-1.99 2S5.9 22 7 22s2-.9 2-2-.9-2-2-2zm10 0c-1.1 0-1.99.9-1.99 2s.89 2 1.99 2 2-.9 2-2-.9-2-2-2zm-9.83-3.25l.03-.12.9-1.63h7.45c.75 0 1.41-.41 1.75-1.03l3.86-7.01L19.42 4h-.01l-1.1 2-2.76 5H8.53l-.13-.27L6.16 6l-.95-2-.94-2H1v2h2l3.6 7.59-1.35 2.45c-.16.28-.25.61-.25.96 0 1.1.9 2 2 2h12v-2H7.42c-.13 0-.25-.11-.25-.25z"></path></svg>
								添加到编辑列表
							</button>-->
						</div>
					</div>
					<div id="wizard_palicannon_par_select_text_body">
					</div>
				</div>
				<div class="enter"></div>
			</div>
		<div id="palicannon_par_res_list_shell">
		<div id="palicannon_par_res_list">
		</div>
		</div>
	</div>
</div>
		
</div>
	<!--  Tool bar on right side -->
	<div id="tool_btn" class="right_tool_btn" style="display: none;">
		<button style="border: 2px solid var(--nocolor); background-color: unset;"><a href="#toc_root">
			<svg t="1596888255722" class="icon" style="height: 3em; width: 3em;" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="4368" width="200" height="200">
				<path d="M510.4 514.2m-449.2 0a449.2 449.2 0 1 0 898.4 0 449.2 449.2 0 1 0-898.4 0Z" fill="#8a8a8a" p-id="4369"></path>
				<path d="M719.4 314.5h-416c-8.5 0-15.4-6.9-15.4-15.4v-4.8c0-8.5 6.9-15.4 15.4-15.4h416.1c8.5 0 15.4 6.9 15.4 15.4v4.8c-0.1 8.4-7 15.4-15.5 15.4z" fill="#ffffff" p-id="4370"></path>
				<path d="M494.1 735.1v-416c0-8.5 6.9-15.4 15.4-15.4h4.8c8.5 0 15.4 6.9 15.4 15.4v416.1c0 8.5-6.9 15.4-15.4 15.4h-4.8c-8.4-0.1-15.4-7-15.4-15.5z" fill="#ffffff" p-id="4371"></path>
				<path d="M672.5 503.1l-165-165c-6-6-6-15.8 0-21.7l3.4-3.4c6-6 15.8-6 21.7 0l165 165c6 6 6 15.8 0 21.7l-3.4 3.4c-6 6-15.7 6-21.7 0z" fill="#ffffff" p-id="4372"></path>
				<path d="M325.2 478l165-165c6-6 15.8-6 21.7 0l3.4 3.4c6 6 6 15.8 0 21.7l-165 165c-6 6-15.8 6-21.7 0l-3.4-3.4c-5.9-6-5.9-15.7 0-21.7z" fill="#ffffff" p-id="4373"></path>
			</svg>
		</a>
		</button>
	</div>
	<div id="right_tool_bar" onmouseover="editor_show_right_tool_bar(true)">
		<div id="right_tool_bar_title">
		<button class="res_button" style="padding: 0" onclick="editor_show_right_tool_bar(false)">
			<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_clear"></use></svg>
		</button>
		</div>
		<div id="right_tool_bar_inner">
		<div id="pc_res_loader">
			<div id="pc_res_load_button">
				<button  id="id_open_editor_load_stream"  onclick="open_editor_load_stream()"><?php //echo $module_gui_str['editor']['1030'];?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_cloud_download"></use></svg>
				</button>
				<button  id="id_cancel_stream" onclick="append_stream()"><?php //echo $_local->gui->cancel;?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_note_add"></use></svg>
				</button>
				<button  id="pc_empty_download_list" onclick="pc_empty_download_list()"><?php //echo $module_gui_str['editor']['1045'];?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_delete"></use></svg>
				</button>
				<button onclick="get_pc_res_download_list_from_cookie()"><?php //echo $_local->gui->refresh;?>
					<svg class="button_icon"><use xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="svg/icon.svg#ic_autorenew"></use></svg>
				</button>
				<input type="checkbox" style="display:none;" checked /><?php //echo $module_gui_str['editor']['1088'];?>
			</div>
			
			<div id="pc_res_list_div">
			</div>
			<div id="id_book_res_load_progress"></div>
			<canvas id="book_res_load_progress_canvas" width="300" height="30"></canvas>
		</div>
		</div>
	</div>
	<!--  Tool bar on right side end -->
	<script>
	 window.addEventListener('scroll',winScroll);
	function winScroll(e){ 
		if(GetPageScroll().y>150){
			$("#search_toolbar_1").css("top",0) ;
		}
		else{
			$("#search_toolbar_1").css("top",GetPageScroll().y - 150) ;
		}
		if(GetPageScroll().y>$(window).height()*0.9){
			$("#tool_btn").show();
		}
		else{
			$("#tool_btn").hide();
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
	<?php
require_once '../studio/index_foot.php';
?>

