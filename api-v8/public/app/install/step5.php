<?php
require_once "install_head.php";
?>
<html>
<head>
</head>
<body>
<style>
#step5{
	background-color: #f1e7a4;
}
</style>
<?php
require_once 'nav_bar.php';
?>
<h3>Step 4 Build Pali Canon Database 建立三藏语料数据库</h3>

<div class="card" style="background-color:#f1e7a4;">
你可以下载已经制作好的语料数据库放在项目文件夹中
<a href="https://www.dropbox.com/s/naf7sk9i9sf0dfi/appdata.7z?dl=0">drobox 7z format 754MB</a>
解压缩后放在项目tmp目录中
<pre>
[tmp]
 └appdata
   └dict
     └3rd
	 └system
   └palicanon
 └user
 </pre>
</div>
<?php
$dbfile[] = array(_FILE_DB_BOLD_, "bold.sql");
$dbfile[] = array(_FILE_DB_INDEX_, "index.sql");
$dbfile[] = array(_FILE_DB_BOOK_WORD_, "bookword.sql");
$dbfile[] = array(_FILE_DB_PALI_INDEX_, "paliindex.sql");
$dbfile[] = array(_FILE_DB_WORD_INDEX_, "wordindex.sql");
$dbfile[] = array(_FILE_DB_PALI_SENTENCE_, "pali_sent.sql");
$dbfile[] = array(_FILE_DB_PALITEXT_, "pali_text.sql");
$dbfile[] = array(_FILE_DB_RESRES_INDEX_, "res.sql");
$dir = "./palicanon_db/";

if (isset($_GET["index"])) {
	/*
    echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;background-color:yellow;">';
    $index = $_GET["index"];
    $dns = "" . $dbfile[$index][0];
    $dbh = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
    $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    //建立数据库
    $_sql = file_get_contents($dir . $dbfile[$index][1]);
    $_arr = explode(';', $_sql);
    //执行sql语句
    foreach ($_arr as $_value) {
        $dbh->query($_value . ';');
    }
    echo $dns . "建立数据库成功";
    echo "</div>";
	*/
}
?>

<div class="card">
<h4>拆分html文件</h4>
<a href="xmlmaker.php">拆分</a><br>
<a href="../../log/palicanoon.log" target="_blank">view log file</a>
</div>

<div class="card">
<h4>逐词解析模板数据库</h4>
<?php
if (file_exists(_DIR_PALICANON_TEMPLET_)) {
    $iCount = 0;
    for ($i = 1; $i <= 217; $i++) {
        if (file_exists(_DIR_PALICANON_TEMPLET_ . "/p{$i}_tpl.db3")) {
            $iCount++;
        }
    }
    if ($iCount == 217) {
        echo "文件已经存在。<br>";
        echo '<a href="db_insert_templet.php">重新生成数据库</a>';
    } else {
        echo "缺少" . (217 - $iCount) . "个文件<br>";
        echo '<a href="db_insert_templet.php">生成数据库</a>';
    }
} else {
    echo "模板数据库目录不存在<br>";
}
?>
</div>

<div class="card">
	<h4>单词索引表</h4>

	
	<div class="contence">
	<!--
	<a href="db_insert_index.php" target="_blank">生成-一本书一次写入</a><br>
	<a href="db_insert_index_once.php" target="_blank">一次生成所有的书</a><br>
	<a href="db_insert_index_csv.php" target="_blank">生成中间csv文件（项目文档已经有了。无需生成）</a><br>
-->
	<a href="db_insert_bookword_from_csv.php" target="_blank">从csv文件导入书单词索引表（bookword）</a><br>
	<a href="db_insert_word_from_csv.php" target="_blank">从csv文件导入单词表(paliindex)</a><br>
	<a href="db_insert_wordindex_from_csv.php" target="_blank">从csv文件导入单词索引表(wordindex)</a><br>

	</div>
</div>

<div class="card">
	<h4>黑体字数据库</h4>
	<?php
    echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;">';

    echo "</div>";
    ?>
	<div class="contence">
	<a href="db_insert_bold.php" target="_blank">生成</a>
	</div>
</div>

<div class="card">
    <h4>Pali句子库</h4>
    <div style="padding:10px;margin:5px;border-bottom: 1px solid gray;display:flex;">

    </div>
    <div class="contence">
    <a href="db_insert_sentence.php">重新生成</a>
    </div>
</div>

<div class="card">
<h4>Pali原文库</h4>
<div>
<?php
$db = $dbfile[6];
echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;">';



echo '<a href="db_insert_palitext.php">生成</a><br>';
echo '<a href="db_update_palitext.php" target="_blank">更新</a><br>';

echo "<a href = '" . _DIR_LOG_ . "/db_update_palitext.log" . "' target='_blank'>view Log</a>"
?>
</div>
</div>


<div class="card">
<h4>标题索引</h4>
<div>
<?php
$db = $dbfile[7];
echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;display:flex;">';

echo '<div style="flex:3;">';
echo '<a href="db_update_toc.php" target="_blank">更新</a><br>';
echo "</div>";

echo "<a href = '" . _DIR_LOG_ . "/db_update_title.log" . "' target='_blank'>view Log</a>"
?>
</div>
</div>

<hr>
<h2>完成</h2>
</body>
</html>