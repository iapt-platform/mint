<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" >

	<script language="javascript" src="../article/my_article.js"></script>
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
			<div><a href="../article/my_article_index.php">返回</a><span id="article_title"></span></div>
			<div>
				<span class="icon_btn_div">
					<span class="icon_btn_tip"></span>
					<button id="file_add" type="button" class="icon_btn" title=" ">
						<a href="../course/my_course_index.php?op=new">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
						</svg>
						</a>
					</button>
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
	
<script>
my_article_edit("<?php echo $_GET["id"] ?>");
</script>
<?php
require_once '../studio/index_foot.php';
?>

