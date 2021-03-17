<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="channal_list_init()">

	<script language="javascript" src="../ucenter/name_selector.js"></script>
	<script language="javascript" src="../channal/channal_add_dlg.js"></script>
	<script language="javascript" src="../channal/channal.js"></script>
	<script src="../guide/guide.js"></script>
	<link type="text/css" rel="stylesheet" href="../guide/guide.css"/>
	<script >
	var gCurrPage="channal";
	</script>
	<script src="../public/js/marked.js"></script>

	<style>
	#channal {
		background-color: var(--btn-border-color);
		
	}
	#channal:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div id="channal_list"  class="file_list_block">

			<div class="tool_bar">
				<div>
					<?php echo $_local->gui->channels; ?>
				</div>

				<div>
					<span class="icon_btn_div">
						<span class="icon_btn_tip"><?php echo "Add";?></span>
						<button id="file_add" type="button" class="icon_btn" title=" "  onclick="channal_add_dlg_show()">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
							</svg>
						</button>
						<div id='channal_add_div' class="float_dlg"></div>
					</span>
					
					<span class="icon_btn_div">				
						<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin;?></span>
						<button id="to_recycle" type="button" class="icon_btn" onclick="file_del()" title=" ">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_delete"></use>
							</svg>
						</button>
					</span>	
				</div>
					
			</div>

			<div id="my_channal_list">		</div>
			
		</div>
		
	</div>
	
<div class="modal_win_bg">
	<div id="share_win" class="iframe_container"></div>
</div>

<?php
require_once '../studio/index_foot.php';
?>

