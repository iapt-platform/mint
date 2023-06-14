<?php

require_once '../config.php';
require_once "../public/_pdo.php";

if (isset($_GET["language"])) {$currLanguage = $_GET["language"];} else { $currLanguage = "en";}

if (isset($_GET["device"])) {$currDevice = $_GET["device"];} else { $currDevice = "computer";}

if (isset($_GET["item"])) {$currSettingItem = $_GET["item"];} else { $currSettingItem = "local_gramma";}

$album_power["15"] = "超级管理员";
$album_power["1"] = "管理员";
$album_power["2"] = "编辑";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link type="text/css" rel="stylesheet" href="../app/css/main.css"/>
	<link type="text/css" rel="stylesheet" href="../app/css/setting.css"/>
	<title>PCD Studio</title>
	<script language="javascript" src="js/common.js"></script>
	<script>
		g_device="computor";
	</script>
</head>
<body class="mainbody" id="mbody">
	<div class="main">
		<!-- content begin-->
		<div id="leftmenuinner">
			<div class="toolgroup1">

			</div>

			<div >
				<h1>Setting</h1>
			</div>

			<div class='toc' id='leftmenuinnerinner'>
				<ul class="setting_item">
					<li><a href="setting.php?item=general">General</a></li>
					<li><a href="setting.php?item=studio">Studio</a></li>
					<li><a href="setting.php?item=liberay">Liberay</a></li>
					<li><a href="setting.php?item=dictionary">Dictionary</a></li>
					<li><a href="setting.php?item=userdict">User Dictionary</a></li>
					<li><a href="setting.php?item=term">Term</a></li>
					<li><a href="setting.php?item=message">Message</a></li>
					<li><a href="setting.php?item=album">Album</a></li>
					<li><a href="setting.php?item=share">Share</a></li>
					<li><a href="setting.php?item=account">Accont</a></li>
				</ul>
			</div>
		</div>

		<div id="setting_main_view" class="mainview">
		<div class="tool_bar">
			<div >
			<?php
switch ($currSettingItem) {
    case "account":
        break;
    case "album":
        if (isset($_GET["id"])) {
            echo "<a href='setting.php?item=album'>返回</a>";
        }
        break;
}
?>

			</div>
			<div>
				<span>Language</span>
				<select id="id_language" name="menu" >
					<option value="en" >English</option>
					<option value="sinhala" >සින‍්හල</option>
					<option value="zh" >简体中文</option>
					<option value="tw" >正體中文</option>
				</select>
			</div>
		</div>
<?php
switch ($currSettingItem) {
    case "account":
        PDO_Connect(_FILE_DB_USERINFO_);
        $query = "SELECT * from "._TABLE_USER_INFO_." where 1 limit 0,1000";
        $user_info = PDO_FetchAll($query);
        echo "<table>";
        echo "<tr><th>id</th><th>user name</th><th>nick name</th></tr>";

        foreach ($user_info as $user) {
            echo "<tr>";
            echo "<td>{$user["id"]}</td>";
            echo "<td>{$user["username"]}</td>";
            echo "<td>{$user["nickname"]}</td>";
            echo "</tr>";
        }

        echo "</table>";
        break;
    case "album":
        $db_file = _FILE_DB_RES_INDEX_;
        PDO_Connect("$db_file");
        echo "<h2>Album</h2>";

        $query = "SELECT * from 'album' where 1 limit 0,1000";
        $Fetch = PDO_FetchAll($query);
        ?>
			<table>
				<tr>
					<th>Book</th><th>Title</th><th>Author</th><th>语言</th><th>媒体</th><th></th><th></th>
				</tr>
			<?php
foreach ($Fetch as $album) {
            echo "<tr><td>{$album["book"]}</td>
						  <td>{$album["title"]}</td>
						  <td>{$album["author"]}</td>
						  <td>{$album["language"]}</td>
						  <td>{$album["type"]}</td>
						  <td><a href=\"../app/album.php?op=show_info&album_id={$album["id"]}\" target='_blank'>详情</a></td>
						  <td><a href=\"../app/album.php?op=export&album_id={$album["id"]}\" target='_blank'>导出</a></td>
						</tr>";
        }
        echo "</table>";
        break;
    case "share":
        PDO_Connect(_FILE_DB_FILEINDEX_);
        $query = "SELECT count(*) from "._TABLE_FILEINDEX_." where share=1";
        $file_count = PDO_FetchOne($query);
        echo "共计：{$file_count} 个共享文件";
        $query = "SELECT * from "._TABLE_FILEINDEX_." where share=1 limit 100";
        $file_share = PDO_FetchAll($query);
        echo "<table>";
        echo "<tr><th>id</th><th>user id</th><th>Title</th><th>Size</th><th></th></tr>";

        foreach ($file_share as $file) {
            echo "<tr>";
            echo "<td>{$file["id"]}</td>";
            echo "<td>{$file["user_id"]}</td>";
            echo "<td>{$file["title"]}</td>";
            echo "<td>{$file["file_size"]}</td>";
            echo "<td>详情</td>";
            echo "</tr>";
        }

        echo "</table>";
        break;
}

?>

		</div>
	</div>

</body>
</html>
