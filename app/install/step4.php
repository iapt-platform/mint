<?php
require_once "install_head.php";
?>
<html>
<head>
</head>
<body>
<style>
#step4{
	background-color:#f1e7a4;
}
</style>
<?php
require_once 'nav_bar.php';
?>
<h3>Step 4 三藏语料库</h3>
<div style="margin:1em;background-color:#f1e7a4;">
	生成三藏语料库
</div>
<div>
<h4>生成数据库文件</h4>
<?php
$dbfile[] = array(_FILE_DB_BOLD_, "bold.sql");
$dbfile[] = array(_FILE_DB_INDEX_, "index.sql");
$dbfile[] = array(_FILE_DB_BOOK_WORD_, "bookword.sql");
$dbfile[] = array(_FILE_DB_PALI_INDEX_, "paliindex.sql");
$dbfile[] = array(_FILE_DB_WORD_INDEX_, "wordindex.sql");
$dbfile[] = array(_FILE_DB_PALI_SENTENCE_, "pali_sent.sql");
$dbfile[] = array(_FILE_DB_PALITEXT_, "pali_text.sql");
$dir = "./palicanon_db/";

if (isset($_GET["index"])) {
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
}

for ($i = 0; $i < count($dbfile); $i++) {
    $db = $dbfile[$i];
    echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;display:flex;">';
    echo '<div style="flex:5;">' . $db[0] . '</div>';
    echo '<div style="flex:3;">';
    if (!file_exists($db[0])) {
        echo "<span style='color:red;'>数据库不存在</span>";
        echo "</div>";
        echo '<div style="flex:2;"><a href="step4.php?index=' . $i . '">建立</a></div>';
    } else {
        echo "<span style='color:green;'>已存在</span>";
        echo "</div>";
        echo '<div style="flex:2;"><a href="step4.php?index=' . $i . '">重建</a><span style="color:red;">注意！此操作将删除原数据库中所有数据！</span></div>';
    }
    echo "</div>";
}
?>

<hr>
<h2><a href="step5.php">Next</a></h2>
</body>
</html>