<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" >

	<script language="javascript" src="../article/my_article.js"></script>
	<script language="javascript" src="../term/note.js"></script>
	<script language="javascript" src="../term/term.js"></script>
	<script language="javascript" src="../public/js/marked.js"></script>
	<script language="javascript" src="../article/add_to_collect_dlg.js"></script>
	<script language="javascript" src="../channal/channal_select.js"></script>
	<script language="javascript" src="../channal/channal.js"></script>
	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>
	<link type="text/css" rel="stylesheet" href="../public/js/jquery-ui-1.12.1/jquery-ui.css"/>
	<script language="javascript" src="../lang/tran_lang_select.js"></script>

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
	.file_list_block {
    max-width: 100%;
	margin-right: 1em;
	max-height: 85vh;
	}
	.index_inner {
    margin-left: 16em;
    margin-top: 5em;
}
#preview_div {
    flex: 6;
    overflow-y: scroll;
	height: 550px;
	max-height: 75vh;
}

	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner " >
	<form id="article_edit" action="##" onsubmit="return false"  method="POST" >
	<div class="file_list_block">
		<div class="tool_bar" style="width:50%;">
			<div style="display:flex;">
				<button class="icon_btn" title=<?php echo $_local->gui->back ;?>>
					<a href="../article/my_article_index.php" >
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#return"></use>
					</svg>
				</a></button>
				<button onclick='article_preview()'  class="icon_btn" title=<?php echo $_local->gui->preview ;?>>
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#preview"></use>
					</svg>
				</button>
				<div id="article_collect" vui='collect-dlg' ></div>
			</div>
			<div style="display:flex;">
				<div>
					<button class="icon_btn" title=<?php echo $_local->gui->scan_in_reader ;?>>
						<a href="../article/index.php?id=<?php echo $_GET["id"];?>" target="_blank">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#library"></use>
							</svg>
						</a>
					</button>
				</div>
					<span class="icon_btn_div">
					<span class="icon_btn_tip"></span>
					<button id="edit_save" type="button" class="icon_btn" title=<?php echo $_local->gui->save ;?> onclick="my_article_save()">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#ic_save"></use>
						</svg>
					</button>
				</span>
				
				<span class="icon_btn_div">				
					<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin ;?></span>
					<button id="to_recycle" type="button" class="icon_btn" onclick="article_del()" title=" ">
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
</form>
	</div>
	
<script>
<?php
if(isset($_POST["active"]) && $_POST["active"]=="new"){
	echo "my_article_edit('{$_POST["content"]}');";
}
else if(isset($_GET["id"])){
	echo "my_article_edit('{$_GET["id"]}');";
}

?>
</script>
<?php
require_once '../studio/index_foot.php';
?>

