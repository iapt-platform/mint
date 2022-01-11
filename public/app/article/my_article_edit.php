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
	height: calc(100vh - 7em);
	}
	.index_inner {
    margin-left: 16em;
    margin-top: 5em;
}
#preview_div {
    flex: 6;
    overflow-y: scroll;
	height: calc(100vh - 7em - 60px);
	
}
#preview_div img{
	width: 90%;
}
#preview_inner{
	background-color: var(--bg-color);
	color: var(--main-color);
	padding: 1em;
}

#preview_inner ul{
	display: block;
    list-style-type: none;
    margin-block-start: 1em;
    margin-block-end: 1em;
    margin-inline-start: 0px;
    margin-inline-end: 0px;
    padding-inline-start: 40px;
	text-indent: -1em;

}
#preview_inner p{
	text-indent: 2em;
}
#preview_inner h3 {
    font-size: 110%;
	text-align: center;
}
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner " >
	<form id="article_edit" action="##" onsubmit="return false"  method="POST" >
	<div class="file_list_block">
		<div class="tool_bar" >
			<div style="display:flex;">

				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo $_local->gui->back ;?></span>
					<button id="icon_btn" type="button" class="icon_btn" >
						<a href="../article/my_article_index.php" >
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#return"></use>
							</svg>
						</a>
					</button>
				</span>

				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo $_local->gui->preview ;?></span>
					<button onclick='article_preview()'  class="icon_btn" >
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#preview"></use>
						</svg>
					</button>
				</span>

				
			</div>
			
			<div style="display:flex;">
				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo "自定义书" ;?></span>
					<button id="edit_custom_book" type="button" class="icon_btn"  onclick="my_article_custom_book()">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#convert"></use>
						</svg>
					</button>
				</span>

				<div id="article_collect" vui='collect-dlg' ></div>
				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo $_local->gui->scan_in_reader ;?></span>
					<button type="button" class="icon_btn" >
						<a href="../article/index.php?view=article&id=<?php echo $_GET["id"];?>" target="_blank">
							<svg class="icon">
								<use xlink:href="../studio/svg/icon.svg#library"></use>
							</svg>
						</a>
					</button>
				</span>

				<span class="icon_btn_div">
					<span class="icon_btn_tip"><?php echo $_local->gui->save ;?></span>
					<button id="edit_save" type="button" class="icon_btn"  onclick="my_article_save()">
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
		<div id="article_list" style="">

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

