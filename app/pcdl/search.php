<?php
require_once "../public/config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

if (isset($_GET["op"])) {
    $op = $_GET["op"];
} else {
    echo "no op";
    exit;
}
if (isset($_GET["word"])) {
    $word = $_GET["word"];
} else {
    echo "no word";
    exit;
}
_load_book_index();
PDO_Connect(_FILE_DB_RESRES_INDEX_);
switch ($op) {
    case "pre":
        //查作者
        $query = "select count(*) from 'author' where name like '%$word%'";
        $count = PDO_FetchOne($query);
        if ($count > 0) {
            $query = "select * from 'author' where name like '%$word%' limit 0,5";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo "<div class='search_list_div'><div class='search_type'>作者($count)</div></div>";
                for ($i = 0; $i < $iFetch; $i++) {
                    echo "<div class='search_list_div' onclick=\"pre_search('author','" . $Fetch[$i]["name"] . "')\"><div class='search_item'>" . $Fetch[$i]["name"] . "</div></div>";
                }
            }
        }

        //查标题
        $query = "select count(*) from \""._TABLE_RES_INDEX_."\"  where  title_en like '%$word%' or title like '%$word%'";
        $count = PDO_FetchOne($query);
        $query = "select * from \""._TABLE_RES_INDEX_."\"  where  title_en like '%$word%' or title like '%$word%' limit 0,20";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            PDO_Connect(_FILE_DB_PALITEXT_);
            $arrBookType = json_decode(file_get_contents("../public/book_name/booktype.json"));
            echo "<div class='search_list_div'><div class='search_type'>标题({$count})</div></div>";
            for ($i = 0; $i < $iFetch; $i++) {
                $book = $Fetch[$i]["book"];
                if (substr($book, 0, 1) == 'p') {
                    $book = substr($book, 1);
                }
                $book--;
                $open_link = "onclick='index_render_res_list(" . $Fetch[$i]["book"] . "," . $Fetch[$i]["album"] . "," . $Fetch[$i]["paragraph"] . ")'";
                $bookid = $Fetch[$i]["book"];
                $t1 = $arrBookType[$bookid - 1]->c1;
                $t2 = $arrBookType[$bookid - 1]->c2;
                $t3 = $arrBookType[$bookid - 1]->c3;
                $bookInfo = _get_book_info($bookid);
                $bookname = $bookInfo->title;

                echo "<div class='search_list_div'>";
                echo "<div class='search_item' $open_link><b>{$Fetch[$i]["title"]}</b>";
                echo "<br><span class='media_type'>{$t1}</span>";
                //echo "<span class='media_type'>{$t2}</span> <span class='media_type'>{$t3}</span>";
                $path = "";
                $parent = $Fetch[$i]["paragraph"];
                $deep = 0;
                $sFirstParentTitle = "";
                //循环查找父标题 得到整条路径

                while ($parent > -1) {
                    $query = "SELECT * FROM "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 1";
                    $FetParent = PDO_FetchAll($query,array($bookid,$parent));
                    if ($deep > 0) {
                        $path = "{$FetParent[0]["toc"]}>{$path}";
                    }
                    if ($sFirstParentTitle == "") {
                        $sFirstParentTitle = $FetParent[0]["toc"];
                    }
                    $parent = $FetParent[0]["parent"];
                    $deep++;
                    if ($deep > 5) {
                        break;
                    }
                }
                if (strlen($path) > 0) {
                    $path = substr($path, 0, -1);
                }
                echo "<i>{$bookname}</i> > {$path}";
                echo "</div>";

                echo "</div>";
            }
        }
        PDO_Connect(_FILE_DB_RESRES_INDEX_);
        //查标签
        $query = "SELECT count(*) from \""._TABLE_RES_INDEX_."\" where tag like '%$word%'";
        $count = PDO_FetchOne($query);

        $query = "SELECT * from \""._TABLE_RES_INDEX_."\" where tag like '%$word%' limit 10";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo "<ul class='search_list'>";
            echo "<li class='title'><div>标签($count)</div><div class='ui-icon ui-icon-carat-r'></div></li>";
            for ($i = 0; $i < $iFetch; $i++) {
                $open_link = "onclick='index_render_res_list(" . $Fetch[$i]["book"] . "," . $Fetch[$i]["album"] . "," . $Fetch[$i]["paragraph"] . ")'";

                echo "<li $open_link><div>" . $Fetch[$i]["title"] . "-{$Fetch[$i]["book"]}</div><div class='ui-icon ui-icon-carat-r'></div></li>";
            }
            echo "</ul>";
        }
        break;
    case "author":
        echo "<div id='search_author'>";
        echo "<div id='author_name'>$word</div>";
        echo "<div id='search_body'>";
        //author id
        $query = "SELECT id from 'author' where name = '$word'";
        $arr_author = PDO_FetchAll($query);
        if (count($arr_author) > 0) {
            $author_id = $arr_author[0]["id"];
        }

        //查album
        $query = "SELECT count(*) from 'album' where author = '$author_id'";
        $count = PDO_FetchOne($query);
        if ($count > 0) {
            $query = "SELECT * from 'album' where author = '$author_id' limit 0,10";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo "<div class='search_list_div'><div class='search_type'>专辑($count)</div></div>";
                for ($i = 0; $i < $iFetch; $i++) {
                    $open_link = "onclick='index_render_res_list(" . $Fetch[$i]["book"] . "," . $Fetch[$i]["id"] . ",-1)'";
                    echo "<div class='search_list_div'><div class='search_item' $open_link>" . $Fetch[$i]["title"] . "</div></div>";
                }
            }
        }

        //查资源
        $query = "SELECT count(*) from \""._TABLE_RES_INDEX_."\" where author = '$author_id'";
        $count = PDO_FetchOne($query);
        if ($count > 0) {
            $query = "SELECT * from \""._TABLE_RES_INDEX_."\" where author = '$author_id' limit 0,10";

            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo "<div class='search_list_div'><div class='search_type'>标题($count)</div></div>";
                for ($i = 0; $i < $iFetch; $i++) {
                    $open_link = "onclick='index_render_res_list(" . $Fetch[$i]["book"] . "," . $Fetch[$i]["album"] . "," . $Fetch[$i]["paragraph"] . ")'";

                    echo "<div class='search_list_div' $open_link><div class='search_item'>" . $Fetch[$i]["title"] . "</div></div>";
                }
            }
        }
        echo "</div>";
        echo "</div>";
        break;
    case "album":
        break;
}
