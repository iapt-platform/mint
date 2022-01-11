	<?php
	require_once __DIR__.'/../public/config.php';
	/*
	加载js 语言包
	范例
	<script>
		alert(gLocal.gui.tools);
	</script>
	*/
	if(file_exists($_dir_lang.$currLanguage.".json")){
		echo "var gLocal = ".file_get_contents($_dir_lang.$currLanguage.".json").";";
	}
	else{
		echo "var gLocal = ".file_get_contents($_dir_lang."default.json").";";
	}
	?>
