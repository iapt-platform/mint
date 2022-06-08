<?php
require_once __DIR__.'/../config.php';
require_once __DIR__.'/../public/load_lang.php';

if (isset($_GET["language"])) {
	$currLanguage = $_GET["language"];
	$_COOKIE["language"] = $currLanguage;
} else {
	if (isset($_COOKIE["language"])) {
		$currLanguage = $_COOKIE["language"];
	} else {
		$currLanguage = "en";
		$_COOKIE["language"] = $currLanguage;
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<head>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link type="text/css" rel="stylesheet" href="../pcdl/css/font.css" />
	<link type="text/css" rel="stylesheet" href="../pcdl/css/basic_style.css" />
	<link type="text/css" rel="stylesheet" href="../pcdl/css/style.css" />
	<?php 
	if(isset($_GET["theme"]) && $_GET["theme"]==="dark"){
		?>
	<link type="text/css" rel="stylesheet" href="../pcdl/css/color_night.css"  id="colorchange" />		
		<?php
	}else{
		?>
	<link type="text/css" rel="stylesheet" href="../pcdl/css/color_day.css" id="colorchange" />		
		<?php	
	}
	?>



	<link type="text/css" rel="stylesheet" href="../pcdl/css/style_mobile.css" media="screen and (max-width:800px)">
	<link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">

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
	<link rel="shortcut icon" type="image/png" sizes="196x196" href=“../public/images/favicon/favicon-196.png">

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

	<title id="page_title">wikipāḷi</title>

	<script src="../config.js"></script>

	<script src="../../node_modules/jquery/dist/jquery.js"></script>
	<script src="../public/js/comm.js"></script>
	<script src="../studio/js/fixedsticky.js"></script>
	<script src="../guide/guide.js"></script>
	<link type="text/css" rel="stylesheet" href="../guide/guide.css" />

	<script src="../ucenter/setting.js"></script>

	<script src="../../node_modules/marked/marked.min.js"></script>
	<script src="../../node_modules/mermaid/dist/mermaid.min.js"></script>

	<script src="../public/js/notify.js"></script>
	<link type="text/css" rel="stylesheet" href="../public/css/notify.css" />

	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>
	<link type="text/css" rel="stylesheet" href="../public/js/jquery-ui-1.12.1/jquery-ui.css" />

	<script src="../term/term.js"></script>
	<script src="../term/note.js"></script>
	<script src="../term/term_popup.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term.css" />
	<link type="text/css" rel="stylesheet" href="../term/term_mobile.css" media="screen and (max-width:800px)">

	<?php
	if (isset($_GET["display"]) && $_GET["display"] == "para") {
		echo '<link type="text/css" rel="stylesheet" href="../term/term_para.css"/>';
	}
	?>
	<script src="../channal/channal.js"></script>
	<script src="../term/popup_note.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/popup_note.css" />

	<script src="../term/term_edit_dlg.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term_edit_dlg.css" />
	<script src="../uwbw/wbw_channal_list.js"></script>
	<script src="../usent/historay.js"></script>
	<script src="../usent/chapter_dynamic.js"></script>
	<script src="../term/pali_sim_sent.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/pali_sim_dlg.css" />
	<script src="../term/related_para.js"></script>

	<script src="../widget/iframe_modal_win.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/iframe_modal_win.css"/>

	<script src="../commit/commit.js"></script>
	<link type="text/css" rel="stylesheet" href="../commit/commit.css"/>


	<script src="../inline_dict/inline_dict.js"></script>

	<script src="../widget/click_dropdown.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/click_dropdown.css" />

	<script src="../public/charcode/coverter_my.js"></script>
	<script src="../public/charcode/converter_si.js"></script>
	<script src="../public/charcode/converter_tai_tham.js"></script>
	<script src="../public/charcode/converter_thai.js"></script>
	<script>
		<?php require_once __DIR__.'/../public/load_lang_js.php'; ?>
	</script>

	<script src="../../node_modules/diff/dist/diff.js"></script>
	
	<style>
		body{
			background-color: var(--bg-color);
		}
		.list_with_head {
			display: flex;
			margin: 3px 0;
		}

		.head_img {
			display: inline-flex;
			min-width: 3em;
			height: 3em;
			padding: 0 0px;
			font-size: 60%;
			background-color: gray;
			color: white;
			border-radius: 1.5em;
			text-align: center;
			justify-content: center;
			margin: auto 2px;
			line-height: 3em;
		}

		.card {
			border-radius: 20px;
			box-shadow: 0 5px 7px rgba(0, 0, 0, 0.05);
			margin: 10px 0;
			background-color: white;
			padding: 0.7rem;
			display: flex;
			position: relative;
			word-break: break-word;
		}

		.card>.title>a,
		.card>.title>a:link {
			color: var(--main-color);

		}

		.card a:hover {
			color: var(--tool-link-hover-color);
		}

		.card_state {
			position: absolute;
			background-color: darkred;
			color: white;
			padding: 0 6px;
			right: -5px;
		}

		.index_inner {
			margin-left: auto;
			margin-right: auto;
		}

		.card li {
			white-space: normal;
		}

		.card code {
			white-space: normal;
		}
	</style>
</head>