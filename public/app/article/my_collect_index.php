<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="my_collect_init()">

	<script language="javascript" src="../article/my_article.js"></script>
	<script language="javascript" src="../article/my_collect.js"></script>
	<script language="javascript" src="../article/collect_add_dlg.js"></script>
	<script >
	var gCurrPage="collect";
	</script>

	<style>
	#collect {
		background-color: var(--btn-border-color);
		
	}
	#collect:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	</style>

<?php
require_once '../studio/index_tool_bar.php';
?>
		
		<div class="index_inner " style="margin-left: 18em;margin-top: 5em;">
		<div class="file_list_block">
			<div class="tool_bar">
				<div><?php echo $_local->gui->composition.$_local->gui->list ?></div>
				<div>
					<span class="icon_btn_div">
						<span class="icon_btn_tip"></span>
						<button id="file_add" type="button" class="icon_btn" title=" " onclick="collect_add_dlg_show()">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
							</svg>
						</button>
						<div id='collect_add_div' class="float_dlg"></div>
					</span>
					
					<span class="icon_btn_div">				
						<span class="icon_btn_tip">回收站</span>
						<button id="to_recycle" type="button" class="icon_btn" onclick="file_del()" title=" ">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_delete"></use>
							</svg>
						</button>
					</span>	
				</div>
			</div>

			<div id="article_list"  class="file_list_block" style="">

			</div>
		</div>
	</div>
	<div class="modal_win_bg">
	<div id="share_win" class="iframe_container"></div>
<?php
require_once '../studio/index_foot.php';
?>

