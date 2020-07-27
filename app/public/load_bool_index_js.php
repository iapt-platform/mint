	<?php
	require_once '../public/config.php';
	/*
	加载js book list 语言包
	范例
	<script>
		alert(gLocal.gui.tools);
	</script>
	*/
	if(file_exists($_dir_lang.$currLanguage.".json")){
		echo "var local_palicannon_index = ".file_get_contents($_dir_book_index."a/".$currLanguage.".json").";";
	}
	else{
		echo "var local_palicannon_index = ".file_get_contents($_dir_book_index."a/default.json").";";
	}
	?>