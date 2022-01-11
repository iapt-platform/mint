<?php
#已经废弃
require_once "../config.php";
require_once 'checklogin.inc';
require_once "../public/load_lang.php";
require_once '../public/config.php';
require_once "../public/_pdo.php";

//if(isset($_GET["language"])){$currLanguage=$_GET["language"];}
//else{$currLanguage="en";}

if (isset($_GET["device"])) {$currDevice = $_GET["device"];} else { $currDevice = "computer";}

$menu_active["account"] = "";
$menu_active["general"] = "";
$menu_active["local"] = "";
$menu_active["studio"] = "";
$menu_active["liberay"] = "";
$menu_active["dictionary"] = "";
$menu_active["userdict"] = "";
$menu_active["term"] = "";
$menu_active["message"] = "";
$menu_active["album"] = "";
if (isset($_GET["item"])) {
    $currSettingItem = $_GET["item"];
    $menu_active[$currSettingItem] = " class='act'";
} else {
    $currSettingItem = "home";
}

$album_power["15"] = "超级管理员";
$album_power["1"] = "管理员";
$album_power["2"] = "编辑";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<link type="text/css" rel="stylesheet" href="css/main.css"/>
	<link type="text/css" rel="stylesheet" href="css/setting.css"/>
	<title>PCD Studio</title>
	<script src="../public/js/jquery.min.js"></script>
	<script type="text/javascript">
	$(document).ready(function(){
	$(".flip").click(function(){
		$(this).next().slideToggle("slow");
	  });
	});
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
					<li <?php echo $menu_active["account"]; ?>><a href="setting.php?item=account"><?php echo $_local->gui->account; ?></a></li>
					<li <?php echo $menu_active["general"]; ?>><a href="setting.php?item=general"><?php echo $_local->gui->general; ?></a></li>
					<li <?php echo $menu_active["local"]; ?>><a href="setting.php?item=local">Local</a></li>
					<li <?php echo $menu_active["studio"]; ?>><a href="setting.php?item=studio"><?php echo $_local->gui->studio; ?></a></li>
					<li <?php echo $menu_active["liberay"]; ?>><a href="setting.php?item=liberay"></a><?php echo $_local->gui->library; ?></li>
					<li <?php echo $menu_active["dictionary"]; ?>><a href="setting.php?item=dictionary"><?php echo $_local->gui->dictionary; ?></a></li>
					<li <?php echo $menu_active["userdict"]; ?>><a href="setting.php?item=userdict"><?php echo $_local->gui->userdict; ?></a></li>
					<li <?php echo $menu_active["term"]; ?>><a href="setting.php?item=term"><?php echo $_local->gui->dict_terms; ?></a></li>
					<li <?php echo $menu_active["message"]; ?>><a href="setting.php?item=message"><?php echo $_local->gui->message; ?></a></li>
					<li <?php echo $menu_active["album"]; ?>><a href="setting.php?item=album">Album</a></li>
					<li><a href="../admin/setting.php?item=account">Web Admin</a></li>
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
			<!--
			<div>
				<span>Language</span>
				<select id="id_language" name="menu" >
					<option value="en" >English</option>
					<option value="si" >සින‍්හල</option>
					<option value="zh-cn" >简体中文</option>
					<option value="zh-tw" >正體中文</option>
				</select>
			</div>-->
		</div>
<?php
switch ($currSettingItem) {
    case "dictionary":

        PDO_Connect("../" . FILE_DB_REF);

        $query = "select *  from info where 1";
        $all = PDO_FetchAll($query);

        echo "<h2>参考字典 Refrence Dictionary</h2>";
        echo "<table>";
        echo "<th>选择</th><th>名称</th><th>语言</th><th>简介</th><th>下载</th><th></th>";
        foreach ($all as $dict) {
            echo "<tr>";
            echo "<td><input type='checkbox' checked /></td><td>{$dict["shortname"]}</td><td>{$dict["language"]}</td><td>{$dict["name"]}</td><td><button>下载</button></td><td><a>管理</a></td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<h2>系统字典System Dictionary</h2>";
        break;
    case "local":
        //语言文件
        if (isset($_GET["lang"])) {
            $_local = json_decode(file_get_contents("../public/lang/{$_GET["lang"]}.json"));
        } else {
            $_local = json_decode(file_get_contents("../public/lang/default.json"));
        }
        echo $_local->gui->pcd_studio;
        echo "<button>保存</button>";
        foreach ($_local as $x => $value) {
            echo "<div>";
            $counter = count($value);
            echo "<div class='flip'>{$x}({$counter})</div>";
            echo "<div class='panel' style='display:none;'>";
            if (is_object($value)) {
                $counter = 1;
                echo "<table>";
                foreach ($value as $row => $row_value) {
                    echo "<tr>";
                    echo "<td>$counter</td>";
                    echo "<td>{$row}</td>";
                    echo "<td><input type='input' value='{$row_value}' /></td>";
                    echo "</tr>";
                    $counter++;
                }
                echo "</table>";
                echo "<button>增加</button>";
            } else if (is_array($value)) {
                $counter = 1;
                echo "<table>";
                foreach ($value as $row) {
                    if (is_object($row)) {
                        echo "<tr>";
                        echo "<td>$counter</td>";
                        echo "<td style=\"text-align:right;\">{$row->id}</td>";
                        echo "<td><input type='input' value='{$row->value}' /></td>";
                        echo "</tr>";
                    } else {
                        echo "unkow<br>";
                    }
                    $counter++;
                }
                echo "</table>";
                echo "<button>增加</button>";
            } else {
                echo "unkow type";
            }
            echo "</div>";
            echo "</div>";
        }

        break;
    case "userdict": //用户字典管理
        break;
    case "term":
        break;
    case "account":
        echo "<h2>{$_local->gui->account}</h2>";
        echo "{$_local->gui->username}：$username<br />";
        echo "{$_local->gui->nick_name}：$nickname<br />";
        echo "{$_local->gui->e_mail}：$email<br />";
        echo "<a href='../ucenter/pwd_set.php'>{$_local->gui->change_password}</a><br>";
        echo "<a href='login.php?op=logout'>{$_local->gui->logout}</a>";
        break;
    case "album":
        $db_file = _FILE_DB_RESRES_INDEX_;
        PDO_Connect("$db_file");
        if (isset($_GET["id"])) {
            if (isset($_GET["power"])) {
                /*权限管理*/
                $query = "select * from 'album' where id='{$_GET["id"]}'";
                $album_info = PDO_FetchAll($query);
                if (count($album_info) > 0) {
                    $query = "select * from 'album_power' where album_id='{$album_info[0]["id"]}'";
                    $Fetch = PDO_FetchAll($query);
                    echo "<h2>专辑权限管理</h2>";
                    echo "<div>{$album_info[0]["title"]}-{$album_info[0]["author"]}</div>";
                    echo "<form>";
                    ?>
					<table>
						<tr>
							<th>序号</th><th>用户</th><th>密码</th><th>权限</th><th></th><th></th>
						</tr>

					<?php
$sn = 1;
                    foreach ($Fetch as $oneline) {
                        echo "<tr>
								  <td>{$sn}</td>
								  <td>{$oneline["user_id"]}</td>
								  <td><input type='input' value='{$oneline["password"]}' /></td>
								  <td>
								  <select>";
                        foreach ($album_power as $x => $value) {
                            if ($oneline["power"] == $x) {
                                $select = "selected";
                            } else {
                                $select = "";
                            }
                            echo "<option value='{$x}' {$select}>{$value}</option>\r\n";
                        }
                        echo "</select>
								  </td>
								  <td><button>修改</button></td>
								  <td><button>删除</button></td>
								  </tr>";
                        $sn++;
                    }
                    echo "<input type='submit' />";
                    echo "</form>";
                }
            } else {
                $query = "select * from 'album' where id='{$_GET["id"]}'";
                $Fetch = PDO_FetchAll($query);
                if (count($Fetch) > 0) {
                    echo "<h2>{$Fetch[0]["title"]}</h2>";
                    echo "<form>";
                    foreach ($Fetch[0] as $x => $value) {
                        echo "<div><span>{$x}</span><span><input type='input' value='{$value}' /></span></div>";
                    }
                    echo "<input type='submit' />";
                    echo "</form>";
                }
            }
        } else {
            echo "<h2>My Album</h2>";

            $query = "select * from 'album' where owner='{$UID}'";
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
							  <td><a href=\"album.php?op=show_info&album_id={$album["id"]}\" target='_blank'>详情</a></td>
							  <td><a href=\"album.php?op=export&album_id={$album["id"]}\" target='_blank'>导出</a></td>
							</tr>";
            }
            echo "</table>";
        }
        break;
}

?>

		</div>
	</div>

</body>
</html>