<?php
/*
获取资源列表
 */
require_once "../config.php";
require_once "../public/_pdo.php";
if (isset($_COOKIE["language"])) {
    $lang = $_COOKIE["language"];
} else {
    $lang = "en";
}
require_once "language/db_{$lang}.php";

if (isset($_GET["book"])) {
    $book = (int)$_GET["book"];
} else {
    echo "no book id";
    exit;
}
if (substr($book, 0, 1) == 'p') {
    $book = substr($book, 1);
}

if (isset($_GET["album"])) {
    $album = $_GET["album"];
} else {
    $album = -1;
}

if (isset($_GET["paragraph"])) {
    $paragraph = (int)$_GET["paragraph"];
} else {
    $paragraph = -1;
}

function format_file_size($size)
{
    if ($size < 102) {
        $str_size = $size . "B";
    } else if ($size < (1024 * 102)) {
        $str_size = sprintf("%.1f KB", $size / 1024);
    } else {
        $str_size = sprintf("%.1f MB", $size / (1024 * 1024));
    }
    return ($str_size);
}

PDO_Connect("" . _FILE_DB_RESRES_INDEX_);

//资源名称
$res_title = "";
if ($album == -1) {
    //查书
    if ($paragraph == -1) {
        //查整本书
        $query = "select * from 'book' where book_id='$book' ";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo "<div id='album_info_head'>";
            $res_title = $Fetch[0]["title"];
            echo "<h2>$res_title</h2>";
            echo "<div class='album_info'>";
            echo "<div class='cover'></div>";
            echo "<div class='infomation'>";
            //标题
            echo "<div class='title'>《" . $res_title . "》</div>";
            echo "<div class='type'>" . $Fetch[0]["c1"] . " " . $Fetch[0]["c2"] . "</div>";
            echo "</div>";
            echo "</div>";

            //相关专辑
            $query = "select album.id,album.title, author.name from album LEFT JOIN author ON album.author = author.id  where album.book='$book' ";
            $Fetch_ather = PDO_FetchAll($query);
            $iFetchAther = count($Fetch_ather);
            if ($iFetchAther > 0) {
                echo "<ul class='search_list'>";
                echo "<li class='title'>相关专辑</li>";
                foreach ($Fetch_ather as $one_album) {
                    $read_link = "reader.php?book=$book&album=" . $one_album["id"] . "&paragraph=$paragraph";
                    $info_link = "index_render_res_list($book," . $one_album["id"] . ",-1)";
                    echo "<li onclick='$info_link' >";
                    echo "<span>{$one_album["title"]}</span>";
                    echo "<div><span class='author_name'>" . $one_album["name"] . "</span><span class='ui-icon-carat-r ui-icon' style='display:inline-block;'></span></span>";
                    echo "</li>";
                }
                echo "</ul>";
            }
            //相关专辑结束
            echo "</div>";

            //目录
            PDO_Connect(_FILE_DB_PALITEXT_);
            $query = "SELECT * FROM "._TABLE_PALI_TEXT_." where book = '{$book}' and ( level>'0' and level<8 ) ";
            $Fetch_Toc = PDO_FetchAll($query);
            $iFetchToc = count($Fetch_Toc);
            if ($iFetchToc > 0) {
                $aLevel = array();
                foreach ($Fetch_Toc as $one_title) {
                    $level = $one_title["level"];
                    if (isset($aLevel["{$level}"])) {
                        $aLevel["{$level}"]++;
                    } else {
                        $aLevel["{$level}"] = 1;
                    }
                }
                ksort($aLevel);
                //找出不是一个的最大层级
                foreach ($aLevel as $x => $x_value) {
                    $maxLevel = $x;
                    if ($x_value > 1) {
                        break;
                    }
                }

                echo "<ul class='search_list'>";
                echo "<li class='title'>章节</li>";
                foreach ($Fetch_Toc as $one_title) {
                    $level = $one_title["level"];
                    if ($maxLevel == $level) {
                        $toc_paragraph = $one_title["paragraph"];
                        $toc_title = $one_title["text"];
                        $info_link = "index_render_res_list($book,-1,$toc_paragraph)";
                        echo "<li onclick='$info_link' >";
                        echo "<span>{$toc_title}</span>";
                        echo "<div><span class='author_name'>2</span><span class='ui-icon-carat-r ui-icon' style='display:inline-block;'></span></span>";
                        echo "</li>";
                    }
                }
                echo "</ul>";
            }
            //目录结束
        }
    } else {
        //查书中的一个段

        PDO_Connect(_FILE_DB_PALITEXT_);
        $query = "SELECT text from "._TABLE_PALI_TEXT_." where book= '{$book}' and  paragraph= '{$paragraph}' ";
        $res_title = PDO_FetchOne($query);
        echo "<div id='album_info_head'>";
        echo "<h2>$res_title</h2>";
        echo "<div class='album_info'>";
        echo "<div class='cover'></div>";
        echo "<div class='infomation'>";
        //书名
        echo "<div class='title'>《{$book_name[$book][0]}》</div>";
        echo "<div class='type'>{$book_name[$book][1]}</div>";
        echo "</div>";
        echo "</div>";

        PDO_Connect(_FILE_DB_RESRES_INDEX_);
        $query = "SELECT resindex.id,resindex.title,resindex.type,resindex.album,author.name from \""._TABLE_RES_INDEX_."\" as resindex LEFT JOIN author ON resindex.author = author.id where resindex.book='$book' and resindex.paragraph=$paragraph and resindex.type<>7 group by resindex.album";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            $res_title = $Fetch[0]["title"];

            //可用资源
            echo "<ul class='search_list'>";
            echo "<li class='title'>可用资源</li>";
            foreach ($Fetch as $one_album) {
                $album_type = $one_album["type"];
                $read_link = ""; //"../reader/?book=$book&album=".$one_album["album"]."&paragraph=$paragraph";
                $info_link = "index_render_res_list($book," . $one_album["album"] . ",$paragraph)";
                echo "<li onclick='$info_link' >";
                echo "<span><span class='media_type'>{$media_type_short[$album_type]}</span>{$one_album["title"]}</span>";
                echo "<div><span class='author_name'>{$one_album["name"]}</span><span class='ui-icon-carat-r ui-icon' style='display:inline-block;'></span></span>";
                echo "</li>";
            }
            echo "</ul>";

        }
        //查共享文档
        PDO_Connect(_FILE_DB_FILEINDEX_);
        $query = "SELECT * from "._TABLE_FILEINDEX_." where book=? and paragraph=?  and status>0 and share>0 order by create_time";
        $Fetch = PDO_FetchAll($query,array($book,$paragraph));
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo "<ul class='search_list'>";
            echo "<li class='title'>共享文档</li>";
            foreach ($Fetch as $one_file) {
                //$read_link="reader.php?book=$book&album=".$one_para["album"]."&paragraph=$paragraph";
                $edit_link = "../app/project.php?op=open&doc_id={$one_file["doc_id"]}";
                echo "<li class='noline'><a href='$edit_link' target='_blank'>" . $one_file["title"] . "</a>-" . $one_file["user_id"] . "</li>";
            }
            echo "</ul>";
        }

        //目录
        PDO_Connect(_FILE_DB_PALITEXT_);
        $query = "SELECT * from \""._TABLE_RES_INDEX_."\" where book = ? and level>'0' and level<8 and paragraph >= ? ";
        $Fetch_Toc = PDO_FetchAll($query,array($book,$paragraph));
        $iFetchToc = count($Fetch_Toc);
        if ($iFetchToc > 1) {
            if ($Fetch_Toc[1]["level"] > $Fetch_Toc[0]["level"]) {
                echo "<ul class='search_list'>";
                echo "<li class='title'>章节</li>";
                for ($iToc = 1; $iToc < $iFetchToc; $iToc++) {
                    if ($Fetch_Toc[$iToc]["level"] <= $Fetch_Toc[0]["level"]) {
                        break;
                    }
                    $level = $Fetch_Toc[$iToc]["level"];
                    $toc_paragraph = $Fetch_Toc[$iToc]["paragraph"];
                    $toc_title = $Fetch_Toc[$iToc]["text"];
                    $info_link = "index_render_res_list($book,-1,$toc_paragraph)";
                    echo "<li onclick='$info_link' >";
                    echo "<span>{$toc_title}</span>";
                    echo "<div><span class='author_name'>2</span><span class='ui-icon-carat-r ui-icon' style='display:inline-block;'></span></span>";
                    echo "</li>";
                }
                echo "</ul>";
            }
        }
        //目录结束
    }
} else {
    //查专辑
    if ($paragraph == -1) {
        //查整张专辑
        $query = "select album.id,album.title,album.file,album.guid,album.type, author.name from album LEFT JOIN author ON album.author = author.id  where album.id='$album' ";

        //$query = "select * from 'album' where id='$album'";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo "<div id='album_info_head'>";
            //标题
            $res_title = $Fetch[0]["title"];
            $album_file_name = $Fetch[0]["file"];
            $album_guid = $Fetch[0]["guid"];
            $album_type = $Fetch[0]["type"];

            echo "<h2>$res_title</h2>";
            echo "<div class='album_info'>";
            echo "<div class='cover'></div>";
            echo "<div class='infomation'>";
            //标题
            echo "<div class='title'>" . $res_title . "</div>";
            //echo "<div class='type'>".$Fetch[0]["c1"]." ".$Fetch[0]["c2"]."</div>";
            echo "<div class='author'>" . $Fetch[0]["name"] . "</div>";
            echo "<div class='media'><span class='media_type'>" . $media_type[$Fetch[0]["type"]] . "</span></div>";
            echo "</div>";
            echo "</div>";

            $read_link = "reader.php?book=$book&album=$album&paragraph=$paragraph";
            echo "<ul class='search_list'>";
            echo "<li class='title'>阅读</li>";
            echo "<li><a class='online_read' style='color: white;' href='$read_link' target='_blank'>在线阅读</a></li>";
            echo "</ul>";

            //下载
            echo "<ul class='search_list'>";
            $query = "select album_ebook.file_name, album_ebook.file_size, file_format.format from album_ebook LEFT JOIN file_format ON album_ebook.file_format=file_format.id where album='$album'";
            $Fetch_ebook = PDO_FetchAll($query);
            if (count($Fetch_ebook) > 0) {
                echo "<li class='title'>下载</li>";
                foreach ($Fetch_ebook as $one_ebook) {
                    $ebook_download_link = "<a href='" . $one_ebook["file_name"] . "' target='_blank'>下载</a>";
                    echo "<li><span class='file_format'>" . $one_ebook["format"] . "</span>" . format_file_size($one_ebook["file_size"]) . "$ebook_download_link</li>";
                }
            }
            echo "</ul>";

            //相关专辑
            echo "<ul class='search_list'>";
            $query = "select album.id,album.title,album.file,album.guid,album.type, author.name from album LEFT JOIN author ON album.author = author.id  where album.book='$book' and album.id != $album ";
            //$query = "select * from 'album' where book='$book' and id != $album";
            $Fetch_ather = PDO_FetchAll($query);
            $iFetchAther = count($Fetch_ather);
            if ($iFetchAther > 0) {
                echo "<li class='title'>相关专辑</li>";
                foreach ($Fetch_ather as $one_album) {
                    $read_link = "reader.php?book=$book&album=" . $one_album["id"] . "&paragraph=$paragraph";
                    echo "<li ><span class='media_type'>{$media_type[$one_album["type"]]}</span><a href='$read_link' target='_blank'>{$one_album["title"]}</a>{$one_album["name"]}</li>";
                }
            }
            echo "</ul>";
            //相关专辑结束
            echo "</div>";

            //目录
            switch ($album_type) {
                case 1:
                case 2:
                case 6:
                    PDO_Connect(_FILE_DB_PALITEXT_);
                    $query = "select * from "._TABLE_PALI_TEXT_." where book = '{$book}' and level>'0' and level<8 ";
                    break;
                case 3:
                    $db_file = '../' . $album_file_name;
                    echo "db album:{$db_file}";
                    PDO_Connect("$db_file");
                    $query = "select * from 'album' where guid='$album_guid'";
                    $Fetch_album = PDO_FetchAll($query);
                    $this_album_id = $Fetch_album[0]["id"];
                    $query = "select * from 'data' where level>'0' and level<8 and album=$this_album_id ";

                    break;
            }
            $Fetch_Toc = PDO_FetchAll($query);
            $iFetchToc = count($Fetch_Toc);
            if ($iFetchToc > 0) {
                $aLevel = array();
                foreach ($Fetch_Toc as $one_title) {
                    $level = $one_title["level"];
                    if (isset($aLevel["{$level}"])) {
                        $aLevel["{$level}"]++;
                    } else {
                        $aLevel["{$level}"] = 1;
                    }
                }
                ksort($aLevel);
                //找出不是一个的最大层级
                foreach ($aLevel as $x => $x_value) {
                    $maxLevel = $x;
                    if ($x_value > 1) {
                        break;
                    }
                }

                echo "<ul class='search_list'>";
                echo "<li class='title'>章节</li>";
                foreach ($Fetch_Toc as $one_title) {
                    $level = $one_title["level"];
                    if ($maxLevel == $level) {
                        $toc_paragraph = $one_title["paragraph"];
                        $toc_title = $one_title["text"];
                        $info_link = "index_render_res_list($book,$album,$toc_paragraph)";
                        echo "<li onclick='$info_link' >";
                        echo "<span>{$toc_title}</span>";
                        echo "<div><span class='author_name'>2</span><span class='ui-icon-carat-r ui-icon' style='display:inline-block;'></span></span>";
                        echo "</li>";
                    }
                }
                echo "</ul>";
            }
            //目录结束

        }
    } else {
        //查专辑中的一个段
        $query = "select * from 'index' where album='$album' and paragraph=$paragraph";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            //标题
            $res_title = $Fetch[0]["title"];
            echo "<h2>$res_title</h2>";

            echo "<div class='album_info'>";
            echo "<div class='cover'></div>";
            echo "<div class='infomation'>";
            //出自专辑 标题
            $query = "select album.id,album.title,album.type,album.file,album.guid, author.name from album LEFT JOIN author ON album.author = author.id  where album.id='$album' ";
            $FetchAlbum = PDO_FetchAll($query);
            if (count($FetchAlbum) > 0) {
                $album_type = $FetchAlbum[0]["type"];
                $album_file_name = $FetchAlbum[0]["file"];
                $album_guid = $FetchAlbum[0]["guid"];
                //专辑标题
                $info_link = "index_render_res_list($book,$album,-1)";
                echo "<div onclick='$info_link' class='title'>" . $FetchAlbum[0]["title"] . "</div>";
            }
            echo "<div class='author'>" . $FetchAlbum[0]["name"] . "</div>";
            echo "<div class='author'><span class='media_type'>" . $media_type[$FetchAlbum[0]["type"]] . "</span></div>";
            echo "</div>";
            echo "</div>";

            //在线阅读
            $read_link = "reader.php?book=$book&album=$album&paragraph=$paragraph";
            echo "<ul class='search_list'>";
            echo "<li class='title'>阅读</li>";
            echo "<li><a class='online_read' style='color: white;' href='$read_link' target='_blank'>在线阅读</a></li>";
            echo "</ul>";

            //相关段落
            //$query = "select album.id,album.title,album.file,album.guid,album.type, author.name from album LEFT JOIN author ON album.author = author.id  where album.book='$book' and album.id != $album ";

            $query = "select idx.id , idx.title , idx.album , idx.type , author.name FROM 'index' as idx LEFT JOIN author ON idx.author = author.id WHERE idx.book='$book' and idx.paragraph=$paragraph and idx.album!=$album and idx.type!=7";
            $Fetch_ather = PDO_FetchAll($query);
            $iFetchAther = count($Fetch_ather);
            if ($iFetchAther > 0) {
                echo "<ul class='search_list'>";
                echo "<li class='title'>相关资源</li>";
                foreach ($Fetch_ather as $one_album) {
                    $read_link = "reader.php?book=$book&album=" . $one_album["album"] . "&paragraph=$paragraph";
                    echo "<li class='noline'>";
                    echo "<span><span class='media_type'>{$media_type[$one_album["type"]]}</span>";
                    echo "<a href='$read_link' target='_blank'>{$one_album["title"]}</a></span><span>{$one_album["name"]}</span></li>";
                }
                echo "</ul>";
            }

            //目录
            switch ($album_type) {
                case 1:
                case 2:
                case 6:
                    PDO_Connect(_FILE_DB_PALITEXT_);
                    $query = "select * from "._TABLE_PALI_TEXT_." where book = '{$book}' and level>'0' and level<8 and paragraph>=$paragraph ";
                    break;
                case 3:
                    $db_file = "../{$album_file_name}";
                    PDO_Connect("$db_file");
                    $query = "select * from 'album' where guid='$album_guid'";
                    $Fetch_album = PDO_FetchAll($query);
                    $this_album_id = $Fetch_album[0]["id"];
                    $query = "select * from 'data' where level>'0' and level<8 and paragraph>=$paragraph  and album=$this_album_id ";
                    break;
            }
            $Fetch_Toc = PDO_FetchAll($query);
            $iFetchToc = count($Fetch_Toc);
            if ($iFetchToc > 1) {
                if ($Fetch_Toc[1]["level"] > $Fetch_Toc[0]["level"]) {
                    echo "<ul class='search_list'>";
                    echo "<li class='title'>章节</li>";
                    for ($iToc = 1; $iToc < $iFetchToc; $iToc++) {
                        if ($Fetch_Toc[$iToc]["level"] <= $Fetch_Toc[0]["level"]) {
                            break;
                        }
                        $level = $Fetch_Toc[$iToc]["level"];
                        $toc_paragraph = $Fetch_Toc[$iToc]["paragraph"];
                        $toc_title = $Fetch_Toc[$iToc]["text"];
                        $info_link = "index_render_res_list($book,$album,$toc_paragraph)";
                        echo "<li onclick='$info_link' >";
                        echo "<span>{$toc_title}</span>";
                        echo "<div><span class='author_name'>2</span><span class='ui-icon-carat-r ui-icon' style='display:inline-block;'></span></span>";
                        echo "</li>";
                    }
                    echo "</ul>";
                }
            }
            //目录结束
        }
    }
}
