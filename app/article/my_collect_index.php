<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="my_collect_list()">

	<script language="javascript" src="../article/my_article.js"></script>
	<script language="javascript" src="../article/my_collect.js"></script>

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
				<div>作品列表</div>
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
	
<?php
require_once '../studio/index_foot.php';
?>

