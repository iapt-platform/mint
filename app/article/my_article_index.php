<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body" onLoad="my_article_list()">

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
	</style>

	<?php
	require_once '../studio/index_tool_bar.php';
	?>
		
	<div class="index_inner" style="margin-left: 18em;margin-top: 5em;display:flex;">
		<div id="article_list"  class="file_list_block" style="flex:3;">

		</div>
        <div style="flex:3;"></div>
        <div style="flex:4;"></div>
	</div>
	
<?php
require_once '../studio/index_foot.php';
?>

