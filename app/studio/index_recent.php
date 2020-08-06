<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="indexInit()">
	<script>
		var gCurrPage="recent_scan";
	</script>
	<style>
	#recent_scan {
		background-color: var(--btn-border-color);
		
	}
	#recent_scan:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>
	<script language="javascript" src="js/index_mydoc.js"></script>
	<script src="../doc/coop.js"></script>

	<?php
	require_once 'index_tool_bar.php';
	?>
	<script>
		document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
	</script>
		
	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div class="file_list_block">
			<div class="tool_bar">
				<div>
				<?php echo $_local->gui->recent_scan;?>
				</div>
				<div>
				

				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo $_local->gui->share;?></span>
					<button id="file_share" type="button" class="icon_btn" onclick="file_share(true)" title=" ">
						<svg class="icon">
							<use xlink:href="./svg/icon.svg#ic_add_person"></use>
						</svg>
					</button>
				</span>

				
				<span class="icon_btn_div">				
					<span class="icon_btn_tip"><?php echo $_local->gui->undo_shared;?></span>
					<button id="file_unshare" type="button" class="icon_btn" onclick="file_share(false)" >
						<svg class="icon" style="fill: var(--btn-border-color);">
							<use xlink:href="./svg/icon.svg#ic_add_person"></use>
						</svg>
					</button>
				</span>
				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo $_local->gui->add_folder;?></span>
					<button id="add_folder" type="button" class="icon_btn" onclick="setNaviVisibility('')" title=" ">
						<svg class="icon">
							<use xlink:href="./svg/icon.svg#ic_add_folder"></use>
						</svg>
					</button>
				</span>
				
				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo $_local->gui->add_to_folder;?></span>
					<button id="move_to_folder" type="button" class="icon_btn" onclick="setNaviVisibility('')" title=" ">
						<svg class="icon">
							<use xlink:href="./svg/icon.svg#ic_folder_move"></use>
						</svg>
					</button>
				</span>
				
				<span class="icon_btn_div">				
					<span class="icon_btn_tip"><?php echo $_local->gui->rename;?></span>
					<button id="edit_title" type="button" class="icon_btn" onclick="setNaviVisibility('')" title=" ">
						<svg class="icon">
							<use xlink:href="./svg/icon.svg#ic_rename"></use>
						</svg>
					</button>
				</span>	
				
				<span class="icon_btn_div">				
					<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin;?></span>
					<button id="to_recycle" type="button" class="icon_btn" onclick="file_del()" title=" ">
						<svg class="icon">
							<use xlink:href="./svg/icon.svg#ic_delete"></use>
						</svg>
					</button>
				</span>	
				
				</div>
			</div>
			<div  id="file_filter" style="display:none;">
				<div style="display:flex;justify-content: space-between;">
					<div>
						<select id="id_index_status"  onchange="showUserFilaList()">
							<option value="all" >
								<?php echo $module_gui_str['editor_dictionary']['1018'];//全部?>
							</option>
							<option value="share" >
								<?php echo $module_gui_str['tools']['1017'];//已共享?>
							</option>
							<option value="recycle" >
								<?php echo $module_gui_str['tools']['1007'];//回收站?>
							</option>
						</select>					
					</div>
					<div><?php echo $module_gui_str['editor']['1059'];//排序方式?>
						<select id="id_index_orderby"  onchange="showUserFilaList()">
							<option value="accese_time" ><?php echo $module_gui_str['editor']['1060'];//訪問?></option>
							<option value="modify_time" ><?php echo $module_gui_str['editor']['1061'];//修改?></option>
							<option value="create_time" ><?php echo $module_gui_str['editor']['1062'];//創建?></option>
							<option value="title" ><?php echo $module_gui_str['editor']['1063'];//標題?></option>
						</select>					
						<select id="id_index_order"  onchange="showUserFilaList()">
							<option value="DESC" ><?php echo $module_gui_str['editor']['1111'];//降序?></option>
							<option value="ASC" ><?php echo $module_gui_str['editor']['1110'];//升序?></option>
						</select>	
						<button id="file_select" onclick="mydoc_file_select(true)">
							选择
						</button>
					</div>
				</div>
				<div>
				<input id="keyword" type="input"  placeholder='<?php echo $module_gui_str['editor']['1114'].$module_gui_str['editor']['1115'];?>'  onkeyup="file_search_keyup()"/>
				</div>

				<div>
				<?php echo $module_gui_str['tools']['1005'];?>：<span id="tag_list"><span class="tag"><?php echo $module_gui_str['tools']['1006'];?><a>×</a></span></span><input type="input" style="width:10em;">
				</div>
			</div>
			<div id="file_tools" style="display:none;">
				<div  style="display:flex;justify-content: space-between;">
					<div>
						<span id="button_group_nomal" >
						<button onclick="file_del()"><?php echo $module_gui_str['tools']['1009'];//刪除?></button>
						<button onclick="file_share(true)"><?php echo $module_gui_str['tools']['1008'];//共享?></button>
						<button onclick="file_share(false)"><?php echo $module_gui_str['tools']['1010'];//取消共享?></button>
						</span>
						<span id="button_group_recycle" style="dispaly:none">
						<button onclick="file_remove()" style="background-color:red;"><?php echo $module_gui_str['tools']['1016'];//彻底删除?></button>
						<button onclick="file_remove_all()"><?php echo $module_gui_str['tools']['1015'];//清空回收站?></button>
						</span>
					</div>
					<div>
						<button onclick="mydoc_file_select(false)"><?php echo $module_gui_str['editor']['1028'];//取消?></button>
					</div>
				</div>
			</div>
			<div>
				<div class="file_list_row" style="border-top: none;">
					<div class="file_list_col_1"><input type="checkbox" checked /></div>
					<div class="file_list_col_2"><?php echo $_local->gui->title;?></div>
					<div class="file_list_col_3"><?php echo $_local->gui->share;?></div>
					<div class="file_list_col_4"><?php echo $_local->gui->time;?></div>
					<div class="file_list_col_5"><?php echo $_local->gui->size;?></div>
				</div>
			</div>
			<div id="userfilelist">
			<?php echo $module_gui_str['editor']['1065'];?>
			</div>
			
		</div>
		
	</div>
	
	<div class="foot_div">
	<?php echo $module_gui_str['editor']['1066'];?>
	</div>


	<style>
		#rs_doc_coop_win{
			min-height: 2em;
			width: 20em;
			position: absolute;
			background-color: var(--tool-bg-color1);
			padding: 8px;
			border-radius: 4px;
		}
		</style>
	<div id="rs_doc_coop_shell">
	<div id="rs_doc_coop_win" >
	<div id="rs_doc_coop_win_inner" >

	</div>
	<div id="rs_doc_coop_win_foot" >
		<button onclick="file_coop_win_close()">关闭</button>
	</div>
	</div>
	</div>
</body>
</html>

