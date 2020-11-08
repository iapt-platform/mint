<?php
require_once '../path.php';
require_once '../public/load_lang.php';

if(isset($_GET["language"])){
	$currLanguage=$_GET["language"];
	$_COOKIE["language"]=$currLanguage;
}
else{
	if(isset($_COOKIE["language"])){
		$currLanguage=$_COOKIE["language"];
	}
	else{
		$currLanguage="en";
		$_COOKIE["language"]=$currLanguage;
	}
}

?>

<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<link type="text/css" rel="stylesheet" href="../pcdl/css/font.css"/>
    <link type="text/css" rel="stylesheet" href="../pcdl/css/basic_style.css"/>
    <link type="text/css" rel="stylesheet" href="../pcdl/css/style.css"/>
    <link type="text/css" rel="stylesheet" href="../pcdl/css/color_day.css" id="colorchange" />
    <link type="text/css" rel="stylesheet" href="../pcdl/css/style_mobile.css" media="screen and (max-width:767px)">
	<link href="https://fonts.googleapis.com/css2?family=Padauk:wght@400;700&display=swap" rel="stylesheet">

    <title>wikipāḷi</title>

	<script src="../public/js/jquery.js"></script>
	<script src="../public/js/comm.js"></script>
	<script src="../studio/js/fixedsticky.js"></script>
	<script src="../guide/guide.js"></script>
	<link type="text/css" rel="stylesheet" href="../guide/guide.css"/>

	<script src="../public/js/marked.js"></script>
	<script src="../public/js/mermaid.min.js"></script>

	<script src="../public/js/notify.js"></script>
    <link type="text/css" rel="stylesheet" href="../public/css/notify.css"/>

	<script src="../public/js/jquery-ui-1.12.1/jquery-ui.js"></script>
	<link type="text/css" rel="stylesheet" href="../public/js/jquery-ui-1.12.1/jquery-ui.css"/>	

	<script src="../term/term.js"></script>
	<script src="../term/note.js"></script>
	<script src="../term/term_popup.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term.css"/>
	<?php
		if(isset($_GET["display"]) && $_GET["display"]=="para"){
			echo '<link type="text/css" rel="stylesheet" href="../term/term_para.css"/>';
		}
		?>
	<script src="../term/term_edit_dlg.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term_edit_dlg.css"/>	
	<script src="../uwbw/wbw_channal_list.js"></script>
	
	<script >
	<?php require_once '../public/load_lang_js.php';?>
	</script>

	<style>
	.list_with_head{
		display:flex;
	}
	.head_img{
		display: inline-block;
		width: 20px;
		font-size: 14px;
		padding: 2px;
		background-color: gray;
		color: white;
		border-radius: 99px;
		text-align: center;
		margin-right: 0.5em;
		margin-top: 0.5em;
	}
	.card{
		box-shadow: 0 0 10px rgba(0,0,0,0.15);
		font-size: 1em;
		line-height: 1.3;
	}
	.card>.title>a , .card>.title>a:link{
		color: var(--main-color);

	}
	.card a:hover{
		color: var(--tool-link-hover-color);
	}

	.index_inner {
    width: 960px;
    margin-left: auto;
    margin-right: auto;
}
.card li{
	white-space: normal;
}
.card code{
	white-space: normal;
}

	</style>
</head>
