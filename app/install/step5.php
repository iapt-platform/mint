<?php
require_once '../path.php';
?>
<html>
<head>
</head>
<body>
<style>
.card {
    box-shadow: 0 0 10px rgba(0,0,0,0.15);
    font-size: 1em;
    line-height: 1.3;
    margin:1em;
    padding:1em;
    max-width:60em;
    margin-left:auto;
    margin-right:auto;
}
</style>
<h3>Step 4 Build Pali Canon Database 建立三藏语料数据库</h3>

<div class="card" style="background-color:#f1e7a4;">
目前本功能尚未实现。请下载已经制作好的语料数据库放在项目文件夹中
<a href="https://www.dropbox.com/s/naf7sk9i9sf0dfi/appdata.7z?dl=0">drobox 7z format 754MB</a>
解压缩后放在项目目录中
<pre>
[project dir]
 └app
 └appdata
   └dict
     └3rd
	 └system
   └palicanon
 └user 
 </pre>
</div>


<div class="card">
<h4>拆分html文件</h4>
<a href="xmlmaker.php">拆分</a><br>
<a href="../../log/palicanoon.log" target="_blank">view log file</a>
</div>

<div class="card">
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
</div>

<div class="card">
<h4>黑体字数据库</h4>
<div class="contence">
<a href="db_insert_bold.php" target="_blank">生成</a>
</div>
</div>

<div class="card">
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
</div>

<div class="card">
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
</div>


<hr>
<h2><a href="step4.php">Next</a></h2>
</body>
</html>