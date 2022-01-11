<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="indexInit()">

	<script language="javascript" src="js/index_share.js"></script>
	<script >
	var gCurrPage="share_doc";
	</script>

	<style>
	#share_doc {
		background-color: var(--btn-border-color);
		
	}
	#share_doc:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div class="file_list_block">
			<div class="tool_bar">
				<div>
				<?php echo $_local->gui->collaborate;?>
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

			<div id="userfilelist">
				<?php echo $_local->gui->loading;?>
			</div>
			
		</div>
		
	</div>
	
<?php
require_once '../studio/index_foot.php';
?>

