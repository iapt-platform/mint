<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Change DB</h2>
<?php
include "./_pdo.php";
include "./config.php";
include "../app/public.inc";

$dbfile = $_GET["file"];
$from = $_GET["from"];
$to = $_GET["to"];
echo "<h2>$from</h2>";

{
    $db_file = "../appdata/palicanon/translate/$dbfile.db3";
    PDO_Connect("$db_file");
    $query = "select * from album where 1";
    $Fetch = PDO_FetchAll($query);
    $iFetch = count($Fetch);
    echo "找到album记录 $iFetch<br/>";
    if ($iFetch > 0) {
        for ($i = 0; $i < $iFetch; $i++) {
            $album_id = $Fetch[$i]["id"];
            $book = $Fetch[$i]["book"];
            $guid = $Fetch[$i]["guid"];
            $title = $Fetch[$i]["title"];
            $file = $Fetch[$i]["file"];
            $cover = $Fetch[$i]["cover"];
            $language = $Fetch[$i]["language"];
            $author = $Fetch[$i]["author"];
            $target = $Fetch[$i]["target"];
            $summary = $Fetch[$i]["summary"];
            $publish_time = $Fetch[$i]["publish_time"];
            $update_time = $Fetch[$i]["update_time"];
            $edition = $Fetch[$i]["edition"];
            $edition1 = $Fetch[$i]["edition1"];
            $type = $Fetch[$i]["type"];

            PDO_Connect(_FILE_DB_PALITEXT_);

            $query = "select * from pali_text where book = '{$book}' and level > 0 and level < 8";
            $title_data = PDO_FetchAll($query);
            echo "par count:" . count($title_data) . "<br>";
            $par_list = "";
            foreach ($title_data as $oneTitle) {
                $par_list .= " , " . $oneTitle["paragraph"] . " ";
            }
            $par_list = substr($par_list, 3);

            $db_file = "../appdata/palicanon/translate/$dbfile.db3";
            PDO_Connect("$db_file");
            $query = "select * from data where album='$album_id' and paragraph in ($par_list)";
            echo $query . "<br>";
            $paragraph_list = PDO_FetchAll($query);

            PDO_Connect(_FILE_DB_RESRES_INDEX_);
            $query = "select * from album where guid = '$guid'";
            $search_album = PDO_FetchAll($query);
            if (count($search_album) == 0) {
                $query = "INSERT INTO album (id,
				guid,
				title,
				file,
				cover,
				language,
				author,
				target,
				summary,
				publish_time,
				update_time,
				edition,
				edition1,
				type) VALUES (NULL,
				'$guid',
				'" . $title . "',
				'$file',
				'',
				'0',
				'$author',
				'',
				'',
				'1',
				'" . time() . "',
				'1',
				'$edition1',
				'1')";

                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    print_r($error[2]);
                    break;
                }
                //获取刚刚插入的索引号
                $album_index = $PDO->lastInsertId();
            } else {
                $album_index = $search_album[0]["id"];
                $query = "UPDATE album SET title = '$title' ,
				                         file = '$file' ,
										 cover = '$cover' ,
										 language = '$language' ,
										 author = '$author' ,
										 target = '$target' ,
										 summary = '$summary' ,
										 publish_time = '$publish_time' ,
										 update_time = '$update_time' ,
										 edition = '$edition' ,
										 edition1 = '$edition1' ,
										 type = '$type'
				WHERE guid='$guid' ";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    print_r($error[2]);
                    break;
                }
            }

            echo "开始更新索引 " . count($paragraph_list) . "<br />";
            //开始更新索引
            for ($iPar = 0; $iPar < count($paragraph_list); $iPar++) {
                $oneTitle = $paragraph_list[$iPar];
                $query = "select * from \""._TABLE_RES_INDEX_."\" where album = '$album_index' and book='" . $book . "' and paragraph='" . $paragraph_list[$iPar]["paragraph"] . "'";
                $search_title = PDO_FetchAll($query);
                $title_en = $oneTitle["text"];

                //找到已有的记录  更新
                if (count($search_title) != 0) {
                    $query = "UPDATE \""._TABLE_RES_INDEX_."\" SET title = '" . $oneTitle["text"] . "' ,
					                         title_en = '" . $title_en . "' ,
											 language = '1' ,
											 type = '$type',
											 editor = '1' ,
											 share = '1' ,
											 update_time = '" . time() . "'
					WHERE id='" . $search_title[0]["id"] . "' ";
                    $stmt = @PDO_Execute($query);
                    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                        $error = PDO_ErrorInfo();
                        print_r($error[2]);
                        break;
                    }

                } else { 
					# 未找到 插入
                    if (substr($book, 0, 1) == "b") {
                        $book = substr($book, 1);
                    }
                    $query = "INSERT INTO \""._TABLE_RES_INDEX_."\" 
					(id,
					book,
					paragraph,
					title,
					title_en,
					level,
					type,
					language,
					author,
					editor,
					share,
					edition,
					album,
					update_time)
					VALUES (NULL,
					   '" . $book . "',
				       '" . $oneTitle["paragraph"] . "',
				       '" . $oneTitle["text"] . "',
					   '" . $title_en . "',
					   '" . $title_data[$iPar]["level"] . "',
					   '$type',
					   '$language',
					   '1',
					   '1',
					   '1',
					   '1',
					   '$album_index',
					   '" . time() . "')";

                    $stmt = @PDO_Execute($query);
                    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                        $error = PDO_ErrorInfo();
                        print_r($error[2]);
                        break;
                    }
                }
            }

        }
    }

    echo "insert  ok<br>";

}
if ($from >= $to) {
    echo "<h2>齐活！功德无量！all done!</h2>";
} else {
    echo "<script>";
    echo "window.location.assign(\"update_translate.php?from=" . ($from + 1) . "&to=" . $to . "\")";
    echo "</script>";
    echo "正在载入:" . ($from + 1);
}
?>