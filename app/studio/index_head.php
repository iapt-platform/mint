<?php
require_once '../path.php';
require_once '../ucenter/login.php';
require_once '../public/config.php';
require_once '../public/load_lang.php';
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link type="text/css" rel="stylesheet" href="../studio/css/style.css"/>
	<link type="text/css" rel="stylesheet" href="../studio/css/color_day.css" id="colorchange" />
	

	<title id="page_title"><?php echo $_local->gui->pcd_studio; ?></title>

	<script src="../studio/js/common.js"></script>
	<script src="../public/js/jquery.js"></script>	
	<script src="../studio/js/fixedsticky.js"></script>	
	
	<link type="text/css" rel="stylesheet" href="../public/css/notify.css"/>
	<script src="../public/js/notify.js"></script>

	<script src="../public/js/comm.js"></script>


	<script type="text/javascript">
	<?php require_once '../public/load_lang_js.php';//加载js语言包?>
		
		var g_language="en";
		function menuLangrage(obj){
			g_language=obj.value;
			setCookie('language',g_language,365);
			window.location.assign("index.php?language="+g_language);
		}
	
	</script>
</head>