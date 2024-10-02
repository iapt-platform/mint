<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="my_article_init()">

	<script language="javascript" src="../article/my_article.js"></script>
	<script language="javascript" src="../article/article_add_dlg.js"></script>
	<script >
	var gCurrPage="article";
	</script>

	<style>
	#article {
		background-color: var(--btn-border-color);
		
	}
	#article:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
	}
	.file_list_block{
		width:unset;
	}
	.case_dropdown-content>a {
		display:block;
	}
	.case_dropdown-content>.active{
		background-color:gray;
	}

	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner " style="margin-left: 18em;margin-top: 5em;">
		<div class="file_list_block">
			<div class="tool_bar">
				<div><?php echo $_local->gui->text.$_local->gui->list ?></div>
				<div>
					<span class="icon_btn_div">				
						<span class="icon_btn_tip"><?php echo $_local->gui->add ;?></span>
						<button id="file_add" type="button" class="icon_btn" onclick="article_add_dlg_show()" title=" ">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
							</svg>
						</button>
						<div id='article_add_div' class="float_dlg"></div>
					</span>					
					<span class="icon_btn_div">				
						<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin ;?></span>
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
</div>
<?php
require_once '../studio/index_foot.php';
?>

