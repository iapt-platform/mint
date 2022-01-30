<?php
require_once '../config.php';
require_once '../ucenter/login.php';
require_once '../public/config.php';
require_once '../public/load_lang.php';
?>

<!DOCTYPE html>
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">

	<link type="text/css" rel="stylesheet" href="../studio/css/style.css"/>
	<link type="text/css" rel="stylesheet" href="../studio/css/color_day.css" id="colorchange" />

	<!-- generics -->
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon16.png" sizes="16x16">
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon32.png" sizes="32x32">
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon57.png" sizes="57x57">
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon76.png" sizes="76x76">
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon96.png" sizes="96x96">
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon128.png" sizes="128x128">
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon192.png" sizes="192x192">
	<link rel="icon" type="image/png" href="../public/images/favicon/favicon228.png" sizes="228x228">
	<link rel="icon" type="image/png" href="../public/images/favicon/android-chrome-512x512.png" sizes="512x512">

	<!-- Android -->
	<link rel="shortcut icon" type="image/png" sizes="196x196" href="../public/images/favicon/favicon-196.png">

	<!-- iOS -->
	<link rel="apple-touch-icon" href="../public/images/favicon/apple-touch-icon120.png" sizes="120x120">
	<link rel="apple-touch-icon" href="../public/images/favicon/apple-touch-icon152.png" sizes="152x152">
	<link rel="apple-touch-icon" href="../public/images/favicon/apple-touch-icon167.png" sizes="167x167">
	<link rel="apple-touch-icon" href="../public/images/favicon/apple-touch-icon180.png" sizes="180x180">

	<!-- Windows 8 IE 10-->
	<meta name="msapplication-TileColor" content="#FFFFFF">
	<meta name="msapplication-TileImage" content="../public/images/favicon/favicon144.png">

	<!-- Windows 8.1 + IE11 and above -->
	<meta name="msapplication-config" content="../public/images/favicon/browserconfig.xml" />

	<link rel="shortcut icon" href="../public/images/favicon/favicon.ico">
	<link rel="manifest" href="../public/images/favicon/site.webmanifest">
	<link rel="mask-icon" href="../public/images/favicon/safari-pinned-tab.svg" color="#333333">
	
	<title id="page_title"><?php echo $_local->gui->pcd_studio; ?></title>

	<script src="../config.js"></script>

	<script src="../studio/js/common.js"></script>
	<script src="../public/js/jquery.js"></script>	
	<script src="../studio/js/fixedsticky.js"></script>	
	
	<link type="text/css" rel="stylesheet" href="../public/css/notify.css"/>
	<script src="../public/js/notify.js"></script>

	<script src="../public/js/comm.js"></script>
	
	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>
	<link type="text/css" rel="stylesheet" href="../public/js/jquery-ui-1.12.1/jquery-ui.css"/>	

	<script src="../../node_modules/marked/marked.min.js"></script>
	<script src="../../node_modules/mermaid/dist/mermaid.min.js"></script>

	<script src="../term/term.js"></script>
	<script src="../term/note.js"></script>
	<script src="../term/term_popup.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term.css"/>

	<script language="javascript" src="../guide/guide.js"></script>
	<link type="text/css" rel="stylesheet" href="../guide/guide.css"/>




	<script src="../widget/iframe_modal_win.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/iframe_modal_win.css"/>


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