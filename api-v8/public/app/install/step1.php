<?php
require_once "install_head.php";
?>
<html>
<head>
</head>
<body>
<style>
#step1{
	background-color: #f1e7a4;
}
</style>
<?php
require_once 'nav_bar.php';
?>
<h3>Step 1 Create Dir</h3>
<div>
<?php
$dir[]=_DIR_APPDATA_;

$dir[]=_DIR_PALICANON_;
$dir[]=_DIR_PALICANON_TEMPLET_;
$dir[]=_DIR_PALICANON_WBW_;
$dir[]=_DIR_PALICANON_TRAN_;

$dir[]=_DIR_DICT_;
$dir[]=_DIR_DICT_SYSTEM_;
$dir[]=_DIR_DICT_3RD_;

$dir[]=_DIR_USER_BASE_;
$dir[]=_DIR_USER_DOC_;
$dir[]=_DIR_PALI_CSV_;
$dir[]=_DIR_TEMP_;
$dir[]=_DIR_LOG_;

$dir[]=_DIR_IMAGES_;
$dir[]=_DIR_IMAGES_ARTICLE_;
$dir[]=_DIR_IMAGES_COLLECTION_;
$dir[]=_DIR_IMAGES_COURSE_;
$dir[]=_DIR_IMAGES_LESSON_;

foreach($dir as $onedir){
	echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;display:flex;">';
	echo '<div style="flex:7;">'.$onedir.'</div>';
	echo '<div style="flex:3;">';
	
	if(!file_exists($onedir)){
		if(mkdir($onedir)){
			echo "<span style='color:green;'>建立成功</span>";
		}
		else{
			echo "<span style='color:red;'>建立失败</span>";
		}
	}
	else{
		echo "已存在";
	}
	echo '</div>';
	echo "</div>";
}
?>
</div>
<hr>
<h2  style="text-align:center;"><a href="step2.php">Next</a></h2>
</body>
</html>