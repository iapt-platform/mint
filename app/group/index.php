<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="group_list_init()">

	<script language="javascript" src="../ucenter/name_selector.js"></script>
	<script language="javascript" src="../group/group_add_dlg.js"></script>
	<script language="javascript" src="../group/user_select_dlg.js"></script>
	<script language="javascript" src="../group/group.js"></script>
	<script >
	var gCurrPage="group_index";
	<?php 
	if(isset($_GET["id"])){
		echo "var gGroupId ='".$_GET["id"]."';\n";
	}
	if(isset($_GET["list"])){
		echo "var gList ='".$_GET["list"]."';\n";
	}
	else{
		echo "var gList='none';";
	}
	?>
	
	</script>

	<style>
	#group_index {
		background-color: var(--btn-border-color);
		
	}
	#group_index:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	.info_block{
		margin-bottom:2em;
		padding-left:1em;
	}
	#fun_content{
		display:flex;
		margin-left: 16em;
		margin-top: 5em;
	}
	#channal_list{
		flex:8;
	}
	#member_list_shell{
		flex: 4;
    	min-width: 200px;
		visibility: hidden;
	}
	.file_list_block{
		margin-left:0;
		margin-right:1em;
	}
	#button_new_group{
		display:none;
	}
	#delete{
		display:none;
	}
	#button_new_sub_group{
		display:none;
	}
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div id="fun_content" class="index_inner" >
		<div id="channal_list"  class="file_list_block">

			<div class="tool_bar">
				<div>
					<span><a href="../group/index.php"><?php echo $_local->gui->group; ?></a></span>
					<span id="parent_group"></span>
					<span id="curr_group"></span>
				</div>

				<div>
					<span id="button_new_group" class="icon_btn_div">
						<span class="icon_btn_tip"><?php echo $_local->gui->new_group;?></span>
						<button id="file_add" type="button" class="icon_btn" title=" "  onclick="group_add_dlg_show()">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
							</svg>
						</button>
						<div id='group_add_div' class="float_dlg"></div>
					</span>

<!--
					<span id="button_new_sub_group" class="icon_btn_div">
						<span class="icon_btn_tip"><?php echo $_local->gui->new_sub_group;?></span>
						<button id="file_add" type="button" class="icon_btn" title=" "  onclick="group_add_dlg_show()">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
							</svg>
						</button>
						<div id='sub_group_add_div' class="float_dlg"></div>
					</span>
-->
					<span id="delete" class="icon_btn_div">				
						<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin;?></span>
						<button id="to_recycle" type="button" class="icon_btn" onclick="file_del()" title=" ">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_delete"></use>
							</svg>
						</button>
					</span>	
				</div>
			</div>

			<div id="my_group_list">		</div>
			
		</div>
		<div id="member_list_shell"  class="file_list_block">

			<div class="tool_bar">
				<div>
					<span><?php echo $_local->gui->member; ?><span id ="member_number"></span></span>
					<span id="parent_group"></span>
					<span id="curr_group"></span>
				</div>

				<div>
					<span class="icon_btn_div">
						<span class="icon_btn_tip"><?php echo "Add";?></span>
						<button id="member_add" type="button" class="icon_btn" title=" "  onclick="user_select_dlg_show()">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_add_person"></use>
							</svg>
						</button>
						<div id='user_select_div' class="float_dlg"></div>
					</span>
				</div>
			</div>

			<div id="member_list">		</div>
			
		</div>
		
	</div>
	
<?php
require_once '../studio/index_foot.php';
?>

