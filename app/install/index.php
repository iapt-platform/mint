<?php
require_once '../path.php';
?>
<html>
<head>
</head>
<body>
<h3>Step 1 Create Dir</h3>
<div>
<?php
$dir[]=_DIR_APPDATA_;
$dir[]=_DIR_PALICANON_;
$dir[]=_DIR_PALICANON_TEMPLET_;
$dir[]=_DIR_PALICANON_PALITEXT_;
$dir[]=_DIR_PALICANON_WBW_;
$dir[]=_DIR_PALICANON_TRAN_;

$dir[]=_DIR_DICT_;
$dir[]=_DIR_DICT_SYSTEM_;
$dir[]=_DIR_DICT_3RD_;
$dir[]=_DIR_DICT_3RD_;

$dir[]=_DIR_USER_BASE_;
$dir[]=_DIR_USER_DB_;
$dir[]=_DIR_PALI_HTML_;
$dir[]=_DIR_FONT_;
$dir[]=_DIR_DICT_TEXT_;
$dir[]=_DIR_PALI_CSV_;
$dir[]=_DIR_TEMP_;
$dir[]=_DIR_LOG_;
foreach($dir as $onedir){
	if(!file_exists($onedir)){
		if(mkdir($onedir)){
			echo "{$onedir}目录建立成功<br>";
		}
		else{
			echo "{$onedir}目录建立失败<br>";
		}
	}
	else{
		echo "{$onedir}目录已经存在<br>";
	}
}
?>
</div>
<h3>Step 2 Download Pali Canon Html</h3>
<div>
download:
install:

</div>
<h3>Step 3 Build Pali Canon Database</h3>
<div>
<h4>拆分html文件</h4>
<a href="xmlmaker.php">拆分</a><br>
<a href="../../log/palicanoon.log" target="_blank">view log file</a><br>
<h4>逐词解析模板数据库</h4>
<?php
if(file_exists(_DIR_PALICANON_TEMPLET_)){
	$iCount=0;
	for($i=1;$i<=217;$i++){
		if(file_exists(_DIR_PALICANON_TEMPLET_."/p{$i}_tpl.db3")){
			$iCount++;
		}
	}
	if($iCount==217){
		echo "文件已经存在。<br>";
		echo '<a href="db_insert_templet.php">重新生成数据库</a>';
	}
	else{
		echo "缺少".(217-$iCount)."个文件<br>";
		echo '<a href="db_insert_templet.php">生成数据库</a>';
	}
	
}
else{
	echo "模板数据库目录不存在<br>";
}
?>
<h4>Pali句子库</h4>
<?php
if(file_exists(_FILE_DB_PALI_SENTENCE_)){
	echo "Pali句子数据库已经存在<br>";
	echo '<a href="db_insert_sentence.php">重新生成</a>';
}
else{
	echo "Pali句子数据库不存在<br>";
	echo '<a href="db_insert_sentence.php">生成</a>';
}
?>
<h4>Pali原文库</h4>
<div>
<?php
if(file_exists(_FILE_DB_PALITEXT_)){
	echo "Pali原文数据库已经存在";
	echo '<a href="db_insert_palitext.php">重新生成</a>';
}
else{
	echo '<a href="db_insert_palitext.php">生成</a>';
}
?>
</div>
<h3>Step 4 Create User Database</h3>
<div>
</div>
</body>
</html>