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

$sLang["pali"] = 1;
$sLang["en"] = 2;
$sLang["sc"] = 3;
$sLang["tc"] = 4;

$sLang["1"] = "pali";
$sLang["2"] = "en";
$sLang["3"] = "sc";
$sLang["4"] = "tc";

if (isset($_GET["step"])) {
    $step = $_GET["step"];
}

if ($_COOKIE["uid"]) {
    $userid = $_COOKIE["userid"];
    $uid = $_COOKIE["uid"];
} else {
    echo "尚未登录";
    exit;
}

$thisfile = basename(__FILE__);

function new_album($filename, $book, $album_id, $album_type, $album_author, $album_title)
{
    $thisfile = basename(__FILE__);
    echo "<div class='fun_block'>";
    echo "<h2>创建新的专辑</h2>";
    echo "<form action=\"{$thisfile}\" method=\"get\">";
    echo "<input type='hidden' name='step' value='2'/>";
    echo "<input type='hidden' name='filename' value='{$filename}'/>";
    echo "<input type='hidden' name='album_id' value='{$album_id}'/>";
    echo "<input type='hidden' name='album_type' value='{$album_type}'/>";
    echo "<input type='hidden' name='book' value='{$book}'/>";
    echo "Author(必填):<input type='input' name='author' value='{$album_author}'/><br>";
    echo "Title(必填):<input type='input' name='title' value='{$album_title}'/><br>";
    echo "Tag:<input type='input' name='tag' value=''/><br>";
    echo "Summary:<input type='input' name='summary' value=''/><br>";
    echo "Edition:<input type='input' name='edition' placeholder=\"第一版\" value=''/><br>";
    echo "<input type=\"submit\" value='下一步'>";
    echo "</form>";
    echo "</div>";
}

switch ($step) {
    case 1:
        if (isset($_GET["filename"])) {
            $db_file = _FILE_DB_RESRES_INDEX_;
            PDO_Connect("$db_file");
            $album_id = $_GET["id"];
            $filename = $_GET["filename"];
            $book = $_GET["book"];
            $album_type = $_GET["type"];
            $album_author = $_GET["author"];
            $album_title = $_GET["title"];
            $album_lang = $_GET["lang"];
            $query = "select * from 'album' where id='{$album_id}'";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch == 0) { //没有找到专辑guid
                echo "专辑不存在。请指定专辑后再发布。";
            } else //找到专辑guid
            {
                echo "<p>找到专辑</p>";
                if ($Fetch[0]["owner"] == $UID) { //这是自己的专辑
                    $thisfile = basename(__FILE__);
                    echo "正在发布";
                    echo "<script>";
                    echo "window.location.assign(\"{$thisfile}?step=4&filename={$filename}&album_id={$album_id}&book={$book}&album_type={$album_type}\");";
                    echo "</script>";
                } else {
                    echo "<p>此书属于他人。您没有发布权限</p>";
                }
            }
        } else {
            echo "no file name";
            exit;
        }
        break;
    case 3: //选择专辑
        $db_file = _FILE_DB_RESRES_INDEX_;
        PDO_Connect("$db_file");
        $album_type = $_GET["album_type"];
        $book = $_GET["book"];
        $filename = $_GET["filename"];
        $query = "select * from 'album' where book='{$book}' and type='{$type[$album_type]}' and owner='{$uid}'";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch == 0) { //没有找到专辑guid
            echo "没有找到专辑";
        } else {
            echo "<div>发布到</div>";
            echo "<ul class='publish_album_list'>";
            foreach ($Fetch as $oneAlbum) {
                $link = basename(__FILE__) . "?step=4&album_id={$oneAlbum["guid"]}&album_type={$album_type}&book={$book}&filename={$filename}";
                echo "<li><div class='title'><a href='{$link}'>{$oneAlbum["title"]}</a></div>";
                echo "<div class='info'>";
                echo "author:<span>{$oneAlbum["author"]}</span> ";
                echo "Edition:<span>{$oneAlbum["edition"]}</span> ";
                echo "Update:<span>{$oneAlbum["update_time"]}</span>";
                echo "</div>";
                echo "</li>";
            }
            echo "</ul>";
            $album_lang = "en";
            $album_author = $nickname;
            $album_edition = "";
            $album_title = $Fetch[0]["title"];
            $album_id = UUID();

            echo "<div><div onclick='show_new_album()'><a>新建专辑</a></div><div id='div_new_album' style='display:none;'>";
            new_album($filename, $book, $album_id, $album_type, $album_author, $album_title);
            echo "</div></div>";
        }

        break;
    case 4: //发布
        echo "step 4 正在发布数据";
        $album_id = $_GET["album_id"];
        $album_type = $_GET["album_type"];
        $book = $_GET["book"];
        $filename = $_GET["filename"];
        echo "album_type:$album_type";
        switch ($album_type) {
            case "wbw":
                $dbFileName = "../appdata/palicanon/wbw/p" . $book . "_wbw.db3";
                PDO_Connect("$dbFileName");
                $filename = $_GET["filename"];
                $dir = $dir_user_base . $userid . $dir_mydocument;
                $xml = simplexml_load_file($dir . $filename);
                $sDataTableName = "p{$book}_wbw_data";
                $sInfoTableName = "p{$book}_wbw_info";
                $paraList = "(";
                $dataBlock = $xml->xpath('//block');
                foreach ($dataBlock as $block) {
                    if ($block->info->album_id == $album_id) {
                        $paraList .= "'" . $block->info->paragraph . "',";
                    }
                }
                $paraList = mb_substr($paraList, 0, -1, "UTF-8");
                $paraList .= ")";
                echo "paraList:$paraList";

                // 提交更改 删除数据表内容
                $query = "DELETE FROM '{$sDataTableName}' WHERE album_id='{$album_id}' AND paragraph in {$paraList}";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo "delete info table recorders.<br>";
                }
                // 提交更改 删除信息表内容
                $query = "DELETE FROM '{$sInfoTableName}' WHERE album_id='{$album_id}' AND paragraph in {$paraList}";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br/>";
                } else {
                    echo "delete data table recorders.<br/>";
                }

                // 更新infomation表 开始一个事务，关闭自动提交
                $PDO->beginTransaction();

                $query = "INSERT INTO '{$sInfoTableName}' ('id',
										  'album_id',
										  'paragraph',
										  'level',
										  'language',
										  'author',
										  'editor',
										  'version',
										  'edition',
										  'update_time'
										  )
								   VALUES (NULL,?,?,?,?,?,?,?,?,?)";
                $stmt = $PDO->prepare($query);

                $count = 0;
                $arrToc = array();
                $dataBlock = $xml->xpath('//block');
                foreach ($dataBlock as $block) {
                    if ($block->info->album_id == $album_id) {
                        $newData = array(
                            $album_id,
                            $block->info->paragraph,
                            $block->info->level,
                            $sLang["{$block->info->language}"],
                            $block->info->author,
                            $block->info->editor,
                            $block->info->version,
                            $block->info->edition,
                            time(),
                        );
                        $stmt->execute($newData);
                        $count++;
                    }
                }
                // 提交更改
                $PDO->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo " info 表 发布成功。提交 {$count} 条数据。<br />";
                }
                $query = "select id from '{$sInfoTableName}' WHERE album_id='{$album_id}' AND paragraph in {$paraList}";
                $arrInfoId = PDO_FetchAll($query);

                // 开始一个事务，关闭自动提交
                $PDO->beginTransaction();

                $query = "INSERT INTO '{$sDataTableName}' ('id',
										  'info_id',
										  'album_id',
										  'paragraph',
										  'sn',
										  'style',
										  'enter',
										  'word',
										  'real',
										  'type',
										  'gramma',
										  'mean',
										  'note',
										  'part',
										  'partmean'
										  )
								   VALUES (NULL,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
                $stmt = $PDO->prepare($query);

                $count = 0;
                $iIndex = 0;
                $arrToc = array();
                $dataBlock = $xml->xpath('//block');
                foreach ($dataBlock as $block) {
                    if ($block->info->album_id == $album_id) {
                        $paraText = "";
                        $toc = "";

                        foreach ($block->data->children() as $word) {
                            if ($block->info->level > 0 && $block->info->level < 8) {
                                $toc .= $word->pali;
                            }
                            $wordid = str_getcsv($word->id, "-");
                            if (count($wordid) >= 3) {
                                $sn = $wordid[2];
                            } else {
                                $sn = 0;
                            }
                            $newData = array($arrInfoId[$iIndex]["id"],
                                $album_id,
                                $block->info->paragraph,
                                $sn,
                                $word->style,
                                $word->enter,
                                $word->pali,
                                $word->real,
                                $word->type,
                                $word->gramma,
                                $word->mean,
                                $word->note,
                                $word->org,
                                $word->om,
                            );
                            $stmt->execute($newData);
                            $count++;
                        }
                        if ($block->info->level > 0 && $block->info->level < 8) {
                            array_push($arrToc,
                                array("book" => $block->info->book,
                                    "para" => $block->info->paragraph,
                                    "album_id" => $block->info->album_id,
                                    "title" => $toc,
                                    "level" => $block->info->level,
                                    "author" => $block->info->author,
                                    "editor" => $block->info->editor,
                                    "edition" => $block->info->edition,
                                    "lang" => $block->info->language,
                                    "type" => $block->info->type,
                                ));
                        }
                        $iIndex++;
                    }

                }
                // 提交更改
                $PDO->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo " data 发布成功。提交 {$count} 条数据。<br />";
                }

                //更新索引表
                $db_file = _FILE_DB_RESRES_INDEX_;
                PDO_Connect("$db_file");
                echo "开始更新索引 ", count($arrToc), "<br />";
                //开始更新索引
                foreach ($arrToc as $oneTitle) {
                    $query = "select * from 'index' where album = '$album_id' and book='" . $oneTitle["book"] . "' and paragraph='" . $oneTitle["para"] . "'";
                    $search_title = PDO_FetchAll($query);
                    $title_en = pali2english($oneTitle["title"]);
                    //找到已有的记录  更新
                    if (count($search_title) != 0) {
                        $sCurrLang = $oneTitle["lang"];
                        $query = "UPDATE 'index' SET title = '{$oneTitle["title"]}' ,
												 title_en = '{$title_en}',
												 author = '{$oneTitle["author"]}' ,
												 editor = '{$oneTitle["editor"]}' ,
												 share = '1' ,
												 update_time = '" . time() . "'
						WHERE id='" . $search_title[0]["id"] . "' ";
                        $stmt = @PDO_Execute($query);
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            print_r($error[2]);
                            break;
                        } else {
                            echo "更新索引表成功";
                        }

                    } else {
                        /*未找到 插入*/
                        $currType = $oneTitle["type"];
                        $currLang = $oneTitle["lang"];
                        $icurrType = $type["{$currType}"];
                        $icurrLang = $sLang["{$currLang}"];
                        echo "type:$currType lang:$currLang<br>";
                        $book = $oneTitle["book"];
                        if (substr($book, 0, 1) == "b") {
                            $book = substr($book, 1);
                        }
                        $query = "INSERT INTO 'index' (id,
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
						create_time,
						update_time)
						VALUES (NULL,
						   '{$book}',
						   '{$oneTitle["para"]}',
						   '{$oneTitle["title"]}',
						   '{$title_en}',
						   '{$oneTitle["level"]}',
						   '{$icurrType}',
						   '{$icurrLang}',
						   '{$oneTitle["author"]}',
						   '{$oneTitle["editor"]}',
						   '1',
						   '{$oneTitle["edition"]}',
						   '{$album_id}',
						   '" . time() . "',
						   '" . time() . "')";

                        $stmt = @PDO_Execute($query);
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            print_r($error[2]);
                        } else {
                            echo "新建索引记录成功";
                        }
                    }
                }

                break;
            case "translate":
                $dbFileName = "../appdata/palicanon/" . $album_type . "/p" . $book . "_" . $album_type . ".db3";
                PDO_Connect("$dbFileName");
                $filename = $_GET["filename"];
                $dir = $dir_user_base . $userid . $dir_mydocument;
                $xml = simplexml_load_file($dir . $filename);
                $sDataTableName = "p{$book}_translate_data";
                $sInfoTableName = "p{$book}_translate_info";
                $paraList = "(";
                $dataBlock = $xml->xpath('//block');
                foreach ($dataBlock as $block) {
                    if ($block->info->album_id == $album_id) {
                        $paraList .= "'" . $block->info->paragraph . "',";
                    }
                }
                $paraList = mb_substr($paraList, 0, -1, "UTF-8");
                $paraList .= ")";
                echo "paraList:$paraList";

                // 提交更改 删除数据表内容
                $query = "DELETE FROM '{$sDataTableName}' WHERE album_id='{$album_id}' AND paragraph in {$paraList}";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo "delete recorders.";
                }
                // 提交更改 删除信息表内容
                $query = "DELETE FROM '{$sInfoTableName}' WHERE album_id='{$album_id}' AND paragraph in {$paraList}";
                $stmt = @PDO_Execute($query);
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo "delete recorders.";
                }

                // 更新infomation表 开始一个事务，关闭自动提交
                $PDO->beginTransaction();

                $query = "INSERT INTO '{$sInfoTableName}' ('id',
										  'album_id',
										  'paragraph',
										  'level',
										  'language',
										  'author',
										  'editor',
										  'version',
										  'edition',
										  'update_time'
										  )
								   VALUES (NULL,?,?,?,?,?,?,?,?,?)";
                $stmt = $PDO->prepare($query);

                $count = 0;
                $arrToc = array();
                $dataBlock = $xml->xpath('//block');
                foreach ($dataBlock as $block) {
                    if ($block->info->album_id == $album_id) {
                        $newData = array(
                            $album_id,
                            $block->info->paragraph,
                            $block->info->level,
                            $sLang["{$block->info->language}"],
                            $block->info->author,
                            $block->info->editor,
                            $block->info->version,
                            $block->info->edition,
                            time(),
                        );
                        $stmt->execute($newData);
                        $count++;
                    }
                }
                // 提交更改
                $PDO->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo " info 表 发布成功。提交 {$count} 条数据。<br />";
                }
                $query = "select id from '{$sInfoTableName}' WHERE album_id='{$album_id}' AND paragraph in {$paraList}";
                $arrInfoId = PDO_FetchAll($query);

                // 开始一个事务，关闭自动提交
                $PDO->beginTransaction();

                $query = "INSERT INTO '{$sDataTableName}' ('id',
										  'info_id',
										  'album_id',
										  'paragraph',
										  'begin',
										  'end',
										  'text'
										  )
								   VALUES (NULL,?,?,?,?,?,?)";
                $stmt = $PDO->prepare($query);

                $count = 0;
                $iIndex = 0;
                $arrToc = array();
                $dataBlock = $xml->xpath('//block');
                foreach ($dataBlock as $block) {
                    if ($block->info->album_id == $album_id) {
                        $paraText = "";
                        $toc = "";

                        foreach ($block->data->children() as $sen) {
                            if (isset($sen->begin)) {
                                $sent_begin = $sen->begin;
                            } else {
                                $sent_begin = "";
                            }
                            if (isset($sen->end)) {
                                $sent_end = $sen->end;
                            } else {
                                $sent_end = "";
                            }
                            if (isset($sen->text)) {
                                $paraText = $sen->text;
                                if ($block->info->level > 0 && $block->info->level < 8) {
                                    $toc .= $sen->text;
                                }
                            }
                            $newData = array($arrInfoId[$iIndex]["id"],
                                $album_id,
                                $block->info->paragraph,
                                $sent_begin,
                                $sent_end,
                                $paraText,
                            );
                            $stmt->execute($newData);
                            $count++;
                        }
                        if ($block->info->level > 0 && $block->info->level < 8) {
                            array_push($arrToc,
                                array("book" => $block->info->book,
                                    "para" => $block->info->paragraph,
                                    "album_id" => $block->info->album_id,
                                    "title" => $toc,
                                    "level" => $block->info->level,
                                    "author" => $block->info->author,
                                    "editor" => $block->info->editor,
                                    "edition" => $block->info->edition,
                                    "lang" => $block->info->language,
                                    "type" => $block->info->type,
                                ));
                        }

                        $iIndex++;
                    }

                }
                // 提交更改
                $PDO->commit();
                if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                    $error = PDO_ErrorInfo();
                    echo "error - $error[2] <br>";
                } else {
                    echo " data 发布成功。提交 {$count} 条数据。<br />";
                }

                //更新索引表
                $db_file = _FILE_DB_RESRES_INDEX_;
                PDO_Connect("$db_file");
                echo "开始更新索引 ", count($arrToc), "<br />";
                //开始更新索引
                foreach ($arrToc as $oneTitle) {
                    $query = "select * from 'index' where album = '$album_id' and book='" . $oneTitle["book"] . "' and paragraph='" . $oneTitle["para"] . "'";
                    $search_title = PDO_FetchAll($query);
                    $title_en = pali2english($oneTitle["title"]);
                    //找到已有的记录  更新
                    if (count($search_title) != 0) {
                        $sCurrLang = $oneTitle["lang"];
                        $query = "UPDATE 'index' SET title = '{$oneTitle["title"]}' ,
												 title_en = '{$title_en}',
												 author = '{$oneTitle["author"]}' ,
												 editor = '{$oneTitle["editor"]}' ,
												 share = '1' ,
												 update_time = '" . time() . "'
						WHERE id='" . $search_title[0]["id"] . "' ";
                        $stmt = @PDO_Execute($query);
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            print_r($error[2]);
                            break;
                        } else {
                            echo "更新索引表成功";
                        }

                    } else {
                        /*未找到 插入*/
                        $currType = $oneTitle["type"];
                        $currLang = $oneTitle["lang"];
                        echo "type:$currType lang:$currLang<br>";
                        $book = $oneTitle["book"];
                        if (substr($book, 0, 1) == "b") {
                            $book = substr($book, 1);
                        }
                        $query = "INSERT INTO 'index' (id,
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
						create_time,
						update_time)
						VALUES (NULL,
						   '{$book}',
						   '{$oneTitle["para"]}',
						   '{$oneTitle["title"]}',
						   '{$title_en}',
						   '{$oneTitle["level"]}',
						   '{$type["{$currType}"]}',
						   '{$sLang["{$currLang}"]}',
						   '{$oneTitle["author"]}',
						   '{$oneTitle["editor"]}',
						   '1',
						   '{$oneTitle["edition"]}',
						   '{$album_id}',
						   '" . time() . "',
						   '" . time() . "')";

                        $stmt = @PDO_Execute($query);
                        if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                            $error = PDO_ErrorInfo();
                            print_r($error[2]);
                        } else {
                            echo "新建索引记录成功";
                        }
                    }
                }

                break;
            case "note":

                break;
        }

        break;
}
