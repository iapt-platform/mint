<?php
require_once "install_head.php";
?>
<html>
<head>
</head>
<body>
<style>
#step2{
	background-color: #f1e7a4;
}
</style>
<?php
require_once 'nav_bar.php';
?>
<h3>Step 2 Create User Database</h3>
<?php
$dbfile[] = array(_FILE_DB_USER_WBW_, "user_wbw.sql");
$dbfile[] = array(_FILE_DB_COMMENTS_, "comments.sql");
$dbfile[] = array(_FILE_DB_SENTENCE_, "sentence.sql");
$dbfile[] = array(_FILE_DB_TERM_, "dhammaterm.sql");
$dbfile[] = array(_FILE_DB_GROUP_, "group.sql");
$dbfile[] = array(_FILE_DB_USERINFO_, "userinfo.sql");
$dbfile[] = array(_FILE_DB_FILEINDEX_, "fileindex.sql");
$dbfile[] = array(_FILE_DB_WBW_, "wbw.sql");
$dbfile[] = array(_FILE_DB_COURSE_, "course.sql");
$dbfile[] = array(_FILE_DB_MEDIA_, "media.sql");
$dbfile[] = array(_FILE_DB_MESSAGE_, "message.sql");
$dbfile[] = array(_FILE_DB_USER_STATISTICS_, "statistics.sql");
$dir = "./userdb/";

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
        echo '<div style="flex:2;"><a href="step2.php?index=' . $i . '">建立</a></div>';
    } else {
        echo "<span style='color:green;'>已存在</span>";
        echo "</div>";
        echo '<div style="flex:2;"><a href="step2.php?index=' . $i . '">重建</a><span style="color:red;">注意！此操作将删除原数据库中所有数据！</span></div>';
    }
    echo "</div>";
}
?>
<div>
</div>
<hr>
<h2  style="text-align:center;"><a href="step3.php">Next</a></h2>
</body>
</html>