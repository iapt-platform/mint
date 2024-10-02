<!DOCTYPE html>
<html>
<head>
</head>
<body>
<h2>Update Resouce DataBase</h2>
<?php
include "../public/_pdo.php";
include "../public/config.php";
include "../app/public.inc";

if (isset($_GET["res"])) {
    $res_type = $_GET["res"];
} else {
    echo "请输入资源类型";
    exit;
}

if (isset($_GET["from"])) {
    $from = $_GET["from"];
}
if (isset($_GET["to"])) {
    $to = $_GET["to"];
}

switch ($res_type) {
    case "palitext":
        echo "<h2>正在处理 - $res_type - $from</h2>";
        PDO_Connect("" . _FILE_DB_PALITEXT_);
        $query = "select * from pali_text_album where 1";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        echo "找到album记录 $iFetch<br/>";
        if ($iFetch > 0 && $from < $iFetch) {
            $i = $from;
            {
                $album_id = $Fetch[$i]["id"];
                $guid = $Fetch[$i]["guid"];
                $title = $Fetch[$i]["title"];
                $file = _FILE_DB_PALITEXT_;
                $cover = $Fetch[$i]["cover"];
                $language = $Fetch[$i]["language"];
                $author = $Fetch[$i]["author"];
                $target = $Fetch[$i]["target"];
                $summary = $Fetch[$i]["summary"];
                $publish_time = $Fetch[$i]["publish_time"];
                $update_time = $Fetch[$i]["update_time"];
                $edition = $Fetch[$i]["edition"];
                $edition1 = $Fetch[$i]["edition_text"];
                $type = $Fetch[$i]["type"];

                PDO_Connect("" . _FILE_DB_PALITEXT_);
                $query = "select * from pali_text where album_index='$album_id' ";
                $title_data = PDO_FetchAll($query);
                echo "Paragraph Count:" . count($title_data) . "<br>";
                $query = "select count(*) from pali_text where album_index='$album_id'";
                $paragraph_count = PDO_FetchOne($query);

                // 开始一个事务，关闭自动提交
                $PDO->beginTransaction();
                $query = "UPDATE pali_text SET chapter_len = ? , next_chapter = ?, prev_chapter=? , parent= ? WHERE book=? and paragraph=?";
                $stmt = $PDO->prepare($query);

                $paragraph_info = array();
                array_push($paragraph_info, array($from, -1, $paragraph_count, -1, -1, -1));
                for ($iPar = 0; $iPar < count($title_data); $iPar++) {
                    $book = $from + 1;
                    $paragraph = $title_data[$iPar]["paragraph"];

                    if ($title_data[$iPar]["level"] == 8) {
                        $title_data[$iPar]["level"] = 100;
                    }
                    $curr_level = $title_data[$iPar]["level"];

                    $length = -1;
                    for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
                        if ($title_data[$iPar1]["level"] <= $curr_level) {
                            $length = $title_data[$iPar1]["paragraph"] - $paragraph;
                            break;
                        }
                    }
                    if ($length == -1) {
                        $length = $paragraph_count - $paragraph + 1;
                    }

                    $prev = -1;
                    if ($iPar > 0) {
                        for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
                            if ($title_data[$iPar1]["level"] == $curr_level) {
                                $prev = $title_data[$iPar1]["paragraph"];
                                break;
                            }
                        }
                    }

                    $next = -1;
                    if ($iPar < count($title_data) - 1) {
                        for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
                            if ($title_data[$iPar1]["level"] == $curr_level) {
                                $next = $title_data[$iPar1]["paragraph"];
                                break;
                            }
                        }
                    }

                    $parent = -1;
                    if ($iPar > 0) {
                        for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
                            if ($title_data[$iPar1]["level"] < $curr_level) {
                                $parent = $title_data[$iPar1]["paragraph"];
                                break;
                            }
                        }
                    }

                    $newData = array($length,
                        $next,
                        $prev,
                        $parent,
                        $book,
                        $paragraph);
                    $stmt->execute($newData);

                    if ($curr_level > 0 && $curr_level < 8) {
                        array_push($paragraph_info, array($book, $paragraph, $length, $prev, $next, $parent));
                    }
                    //echo "<tr><td>$book</td><td>$paragraph</td><td>$curr_level</td><td>$length</td><td>$prev</td><td>$next</td><td>$parent</td></tr>";
                }

                // 提交更改
                $PDO->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";

                    $log = $log . "$from, $FileName, error, $error[2] \r\n";
                } else {
                    $count = count($title_data);
                    echo "updata $count paragraph info recorders.<br>";
                    echo count($paragraph_info) . " Heading<br>";
                }

                /*

                 */

                $db_file = _FILE_DB_RESRES_INDEX_;
                PDO_Connect("$db_file");
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
				'VRI',
				'',
				'',
				'1',
				'" . time() . "',
				'1',
				'CSCD4',
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
                echo "开始更新索引 ", count($title_data), "<br />";
                //开始更新索引
                foreach ($title_data as $oneTitle) {
                    if ($oneTitle["level"] > 0 && $oneTitle["level"] < 8) {
                        $query = "SELECT * FROM \""._TABLE_RES_INDEX_."\" where album = '$album_index' and book='" . $oneTitle["book"] . "' and paragraph='" . $oneTitle["paragraph"] . "'";
                        $search_title = PDO_FetchAll($query);
                        $title_en = pali2english($oneTitle["text"]);
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
                            /*未找到 插入*/
                            $book = $oneTitle["book"];
                            if (substr($book, 0, 1) == "b") {
                                $book = substr($book, 1);
                            }
                            $query = "INSERT INTO \""._TABLE_RES_INDEX_."\" (id,
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
						   '" . $oneTitle["level"] . "',
						   '1',
						   '1',
						   '5',
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

                foreach ($paragraph_info as $onePar) {
                    $query = "select * from 'paragraph_info' where  book='" . $onePar[0] . "' and paragraph='" . $onePar[1] . "'";
                    $search_par = PDO_FetchAll($query);

                    //找到已有的记录  更新
                    if (count($search_par) != 0) {
                        $query = "UPDATE 'paragraph_info' SET length = '" . $onePar[2] . "' ,
					                         prev = '" . $onePar[3] . "' ,
											 next = '" . $onePar[4] . "' ,
											 parent = '" . $onePar[5] . "'
					WHERE id='" . $search_par[0]["id"] . "' ";
                        $stmt = @PDO_Execute($query);
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            print_r($error[2]);
                            break;
                        }

                    } else {
                        //未找到 插入
                        $query = "INSERT INTO 'paragraph_info' (id,
					book,
					paragraph,
					length,
					prev,
					next,
					parent)
					VALUES (NULL,
					   '" . $onePar[0] . "',
				       '" . $onePar[1] . "',
				       '" . $onePar[2] . "',
					   '" . $onePar[3] . "',
					   '" . $onePar[4] . "',
					   '" . $onePar[5] . "')";

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
        if ($from >= $to) {
            echo "<h2>齐活！功德无量！all done!</h2>";
        } else {
            echo "<script>";
            echo "window.location.assign(\"update_index.php?res=palitext&from=" . ($from + 1) . "&to=" . $to . "\")";
            echo "</script>";
            echo "正在载入:" . ($from + 1);
        }
        break;
    case "heading":

        break;
    case "album":
        PDO_Connect(_FILE_DB_RESRES_INDEX_);
        $query = "select * from album where 1";
        $search_album = PDO_FetchAll($query);
        foreach ($search_album as $oneAlbum) {
            $query = "SELECT id FROM \""._TABLE_RES_INDEX_."\" where album = '{$oneAlbum["id"]}' and
													   book='{$oneAlbum["book"]}' and
													   paragraph='-1'";
            $id = PDO_FetchAll($query);

            //找到已有的记录  更新
            $title_en = pali2english($oneAlbum["title"]);
            if (count($id) > 0) {
                $query = "UPDATE \""._TABLE_RES_INDEX_."\" SET title = '{$oneAlbum["title"]}' ,
										 title_en = '{$title_en}' ,
										 language = '{$oneAlbum["language"]}' ,
										 type = '{$oneAlbum["type"]}',
										 editor = '1' ,
										 share = '1' ,
										 update_time = '" . time() . "'
				WHERE id='{$id[0]["id"]}' ";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    print_r($error[2]);
                    break;
                } else {
                    echo "Updae记录成功。{$oneAlbum["title"]} <br />";
                }

            } else {
                /*未找到 插入*/
                $book = $oneAlbum["book"];
                $query = "INSERT INTO \""._TABLE_RES_INDEX_."\" (id,
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
				   '-1',
				   '{$oneAlbum["title"]}',
				   '{$title_en}',
				   '-1',
				   '{$oneAlbum["type"]}',
				   '{$oneAlbum["language"]}',
				   '{$oneAlbum["author"]}',
				   '1',
				   '1',
				   '{$oneAlbum["edition"]}',
				   '{$oneAlbum["id"]}',
				   '" . time() . "')";

                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    print_r($error[2]);
                    break;
                } else {
                    echo "新建记录成功。{$oneAlbum["title"]} <br />";
                }
            }
        }

        break;
    case "translation":
        break;
    case "note":
        break;
}

?>