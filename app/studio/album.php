<?php
require 'checklogin.inc';
require '../config.php';
require "../public/_pdo.php";
require "./public.inc";

$type["pali"] = 1;
$type["wbw"] = 2;
$type["translate"] = 3;
$type["note"] = 4;
$type["dighest"] = 5;
$type["templet"] = 6;
$type["heading"] = 7;

$iType["1"] = "pali";
$iType["2"] = "wbw";
$iType["3"] = "translate";
$iType["4"] = "note";
$iType["5"] = "dighest";
$iType["6"] = "templet";
$iType["7"] = "heading";

$_lang["1"] = "pali";
$_lang["2"] = "en";
$_lang["3"] = "sc";
$_lang["4"] = "tc";

$_slang["pali"] = "1";
$_slang["en"] = "2";
$_slang["sc"] = "3";
$_slang["tc"] = "4";

$album_power["15"] = "超级管理员";
$album_power["1"] = "管理员";
$album_power["2"] = "编辑";

if (isset($_GET["op"])) {
    $op = $_GET["op"];
}
if (isset($_GET["book"])) {
    $book = $_GET["book"];
}
if (isset($_GET["type"])) {
    $album_type = $_GET["type"];
}

switch ($op) {
    case "show_info":
        $db_file = _FILE_DB_RESRES_INDEX_;
        PDO_Connect("$db_file");
        $album_id = $_GET["album_id"];
        $query = "select * from 'album' where id='{$album_id}'";
        $Fetch = PDO_FetchAll($query);
        if (count($Fetch) > 0) {
            $sFileName = $Fetch[0]["file"];
            $book = $Fetch[0]["book"];
            $type = $Fetch[0]["type"];
            $thisFileName = basename(__FILE__);
            ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">

</head>
<body class="indexbody">
<br/><br/>
			<div class='fun_block' >
			<h2><?php echo $Fetch[0]["title"]; ?></h2>
			<form action=\"{$thisFileName}\" method=\"get\">
			<input type='hidden' name='op' value='update' />
			<input type='hidden' name='album_id' value='<?php echo $album_id; ?>'/>
			<table>
				<tr>
					<td>Type</td><td><?php echo $iType["{$type}"]; ?></td>
				</tr>
				<tr>
					<td>Title</td><td><input type='input' name='title' value='<?php echo $Fetch[0]["title"]; ?>'/></td>
				</tr>
				<tr>
					<td>Book</td><td><?php echo $book; ?></td>
				</tr>
				<tr>
					<td>Author</td><td><input type='input' name='title' value='<?php echo $Fetch[0]["author"]; ?>'/></td>
				</tr>
				<tr>
					<td>Edition</td><td><input type='input' name='title' value='<?php echo $Fetch[0]["edition"]; ?>'/></td>
				</tr>
				<tr>
					<td>Create</td><td><?php echo date("Y-m-d h:i:sa", $Fetch[0]["create_time"]); ?></td>
				</tr>
				<tr>
					<td>Uptate</td><td><?php echo date("Y-m-d h:i:sa", $Fetch[0]["update_time"]); ?></td>
				</tr>
				<tr>
					<td>Cover</td><td><input type="file" name="cover" id="file" /></td>
				</tr>
				<tr>
					<td>Language</td>
					<td>
						<select name="lang" >
							<option value="en">English</option>
							<option value="sc">简体中文</option>
							<option value="tc">正体中文</option>
						</select>
					</td>
				</tr>
				<tr>
					<td><input type="submit" value='Update'></td><td></td>
				</tr>
				</table>
			</form>
			</div>
			<div  class='fun_block' >
			<h2>授权</h2>
<?php
/*权限管理*/
            $query = "select * from 'album' where id='{$_GET["album_id"]}'";
            $album_info = PDO_FetchAll($query);
            if (count($album_info) > 0) {
                $query = "select * from 'album_power' where album_id='{$album_info[0]["id"]}'";
                $Fetch = PDO_FetchAll($query);
                ?>
					<form>
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
                echo "</table>";
                echo "<input type='submit' value='update'/>";
                echo "</form>";
            }

            ?>
			</div>

			<div  class='fun_block' >
				<h2>章节</h2>
				<div>
				<?php
PDO_Connect("$sFileName");
            $table = "p{$book}_{$iType["{$type}"]}_info";
            $query = "SELECT level,title,paragraph FROM '{$table}' WHERE album_id=$album_id and level>0 and level<9";

            //查询章节标题文内容
            $FetchText = PDO_FetchAll($query);
            $iFetchText = count($FetchText);
            if ($iFetchText > 0) {
                echo "<ul>";
                for ($i = 0; $i < $iFetchText; $i++) {
                    $read_link = ""; //"../reader/?book={$book}&album={$album_id}&paragraph={$FetchText[$i]["paragraph"]}";
                    echo "<li class='palicannon_nav_level_{$FetchText[$i]["level"]}'><a href='{$read_link}' target='_blank'>{$FetchText[$i]["title"]}</a></li>";
                }
                echo "</ul>";
            }
            ?>
				</div>
			</div>

			</body>
			</html>
			<?php

        }

        break;
    case "update":
        break;
    case "new_form":
        ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
		<meta name="viewport" content="width=device-width, initial-scale=1.0">
		<link type="text/css" rel="stylesheet" href="css/style.css"/>
		<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
		<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">
	</head>
	<body class="indexbody">
		<br/><br/>
		<div class='fun_block' >
			<h2><?php echo $Fetch[0]["title"]; ?></h2>
			<form action=\"{$thisFileName}\" method=\"get\">
			<input type='hidden' name='op' value='new' />
			<input type='hidden' name='album_id' value='<?php echo $album_id; ?>'/>
			<table>
				<tr>
					<td>Type</td><td><?php echo $iType["{$type}"]; ?></td>
				</tr>
				<tr>
					<td>Title</td><td><input type='input' name='title' value='<?php echo $Fetch[0]["title"]; ?>'/></td>
				</tr>
				<tr>
					<td>Book</td><td><?php echo $book; ?></td>
				</tr>
				<tr>
					<td>Author</td><td><input type='input' name='title' value='<?php echo $Fetch[0]["author"]; ?>'/></td>
				</tr>
				<tr>
					<td>Edition</td><td><input type='input' name='title' value='<?php echo $Fetch[0]["edition"]; ?>'/></td>
				</tr>
				<tr>
					<td>Create</td><td><?php echo date("Y-m-d h:i:sa", time()); ?></td>
				</tr>
				<tr>
					<td>Uptate</td><td><?php echo date("Y-m-d h:i:sa", time()); ?></td>
				</tr>
				<tr>
					<td>Cover</td><td><input type="file" name="cover" id="file" /></td>
				</tr>
				<tr>
					<td>Language</td>
					<td>
						<select name="lang" >
							<option value="en">English</option>
							<option value="sc">简体中文</option>
							<option value="tc">正体中文</option>
						</select>
					</td>
				</tr>
				<tr>
					<td><input type="submit" value='Create'></td><td></td>
				</tr>
				</table>
			</form>
			</div>
		</body>
</html>
			<?php
break;
    case "new":
        $db_file = _FILE_DB_RESRES_INDEX_;
        PDO_Connect("$db_file");
        $album_guid = $_GET["album_guid"];
        $album_type = $_GET["album_type"];
        $book = $_GET["book"];
        $lang = $_GET["lang"];
        $tag = $_GET["tag"];
        $summary = $_GET["summary"];
        $author = $_GET["author"];
        $edition = $_GET["edition"];
        $title = $_GET["title"];
        $dbFileName = _DIR_PALICANON_ . "/" . $album_type . "/p" . $book . "_" . $album_type . ".db3";
        $PDO->beginTransaction();
        $query = "INSERT INTO album (id,
				book,
				guid,
				title,
				file,
				cover,
				language,
				author,
				tag,
				summary,
				create_time,
				update_time,
				version,
				edition,
				type,
				owner) VALUES
				(NULL,
				'{$book}',
				'{$album_guid}',
				'{$title}',
				'{$dbFileName}',
				'',
				'{$_slang[$lang]}',
				'{$author}',
				'{$tag}',
				'{$summary}',
				" . time() . ",
				" . time() . ",
				'1',
				'{$edition}',
				'{$type[$album_type]}',
				'{$UID}')";
        $stmt = @PDO_Execute($query);
        $PDO->commit();
        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            echo "error - $error[2]";
        } else {
            //获取刚刚插入的索引号
            $album_index = $PDO->lastInsertId();
            echo $album_index;
        }

        break;
    case "get":
        $db_file = _FILE_DB_RESRES_INDEX_;
        PDO_Connect("$db_file");
        $query = "select * from 'album' where book='{$book}' and type='{$type[$album_type]}' and owner='{$UID}'";
        $Fetch = PDO_FetchAll($query);
        echo json_encode($Fetch, JSON_UNESCAPED_UNICODE);
        break;
    case "get_album":
        if (isset($_GET["album_id"])) {
            $album_id = $_GET["album_id"];
        }
        if (isset($_GET["book"])) {
            $book = $_GET["book"];
        }
        if (isset($_GET["type"])) {
            $album_type = $_GET["type"];
        }
        $db_file = _FILE_DB_RESRES_INDEX_;
        PDO_Connect("$db_file");
        $query = "select * from 'album' where id='{$album_id}'";
        $Fetch = PDO_FetchAll($query);
        $result = array();
        if (count($Fetch) > 0) { //找到专辑
            $result = array_merge($result, $Fetch);
        }
        //找本人相关专辑
        $query = "select * from 'album' where id!='{$album_id}' and book='{$book}' and type='{$type[$album_type]}' and owner='{$UID}'";
        $Fetch = PDO_FetchAll($query);
        $result = array_merge($result, $Fetch);

        echo json_encode($result, JSON_UNESCAPED_UNICODE);
        break;
}
?>