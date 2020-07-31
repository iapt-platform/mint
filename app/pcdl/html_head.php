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
    <link type="text/css" rel="stylesheet" href="../pcdl/css/color_day.css" id="colorchange" />
    <link type="text/css" rel="stylesheet" href="../pcdl/css/style_mobile.css" media="screen and (max-width:767px)">
    <title>圣典</title>

	<script src="../public/js/jquery.js"></script>
	<script src="../public/js/comm.js"></script>
	<script src="../studio/js/fixedsticky.js"></script>
	<script >
	<?php require_once '../public/load_lang_js.php';?>
	</script>

	<style>
	.card{
		box-shadow: 0 0 10px rgba(0,0,0,0.15);
		font-size: 1em;
		line-height: 1.3;
	}
	.card .title a , a:link{
		color:blue;
	}
	.card a:hover{
		color:blue;
	}

	</style>
</head>
