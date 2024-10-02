<?php
include "../public/config.php";
include "../public/_pdo.php";
if (isset($_COOKIE["language"])) {
    $lang = $_COOKIE["language"];
} else {
    $lang = "en";
}

if (file_exists("language/db_{$lang}.php")) {
    include_once "language/db_{$lang}.php";
} else {
    include_once "language/db_default.php";
}

include "./function.php";

if (isset($_GET["op"])) {
    $op = $_GET["op"];
} else {
    echo "no op";
    exit;
}

PDO_Connect(_FILE_DB_RESRES_INDEX_);
switch ($op) {
    case "all":
        //查热门
        $query = "select * from 'index'  where  1 ORDER BY hit DESC limit 0,10";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo "<ul class='search_list'>";
            echo "<li class='title'>排行</li>";
            for ($i = 0; $i < $iFetch; $i++) {
                $book = $Fetch[$i]["book"];
                if (substr($book, 0, 1) == 'p') {
                    $book = substr($book, 1);
                }
                $open_link = "onclick='index_render_res_list(" . $Fetch[$i]["book"] . "," . $Fetch[$i]["album"] . "," . $Fetch[$i]["paragraph"] . ")'";

                echo "<li>";
                echo "<div class='search_item' $open_link>" . $Fetch[$i]["title"] . "<br/>" . get_book_info_html($book) . "</div>";
                echo "<div>点击：" . $Fetch[$i]["hit"] . "</div>";
                echo "</li>";
            }
            echo "</ul>";
        }

        //查最新
        $query = "select * from 'index'  where  1 ORDER BY create_time DESC limit 0,10";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo "<ul class='search_list'>";
            echo "<li class='title'>最新</li>";
            for ($i = 0; $i < $iFetch; $i++) {
                $book = $Fetch[$i]["book"];
                if (substr($book, 0, 1) == 'p') {
                    $book = substr($book, 1);
                }
                $open_link = "onclick='index_render_res_list(" . $Fetch[$i]["book"] . "," . $Fetch[$i]["album"] . "," . $Fetch[$i]["paragraph"] . ")'";

                $book_info = "《" . $book_name[$book][0] . "》<span>" . $book_name[$book][1] . "</span>";
                echo "<li>";
                echo "<div class='search_item' $open_link><span class='media_type'>" . $media_type[$Fetch[$i]["type"]] . "</span>" . $Fetch[$i]["title"] . "<br/>$book_info</div>";
                echo "<div>" . date("Y-m-d", $Fetch[$i]["create_time"]) . "</div>";
                echo "</li>";
            }
            echo "</ul>";
        }

        break;
    case "new":
        $query = "select * from 'index'  where  1 ORDER BY create_time DESC limit 0,100";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            echo "<ul class='search_list'>";
            for ($i = 0; $i < $iFetch; $i++) {
                $book = $Fetch[$i]["book"];
                if (substr($book, 0, 1) == 'p') {
                    $book = substr($book, 1);
                }
                $open_link = "onclick='index_render_res_list(" . $Fetch[$i]["book"] . "," . $Fetch[$i]["album"] . "," . $Fetch[$i]["paragraph"] . ")'";

                $book_info = "《" . $book_name[$book][0] . "》<span>" . $book_name[$book][1] . "</span>";
                echo "<li>";
                echo "<div class='search_item' $open_link><span class='media_type'>" . $media_type[$Fetch[$i]["type"]] . "</span>" . $Fetch[$i]["title"] . "<br/>$book_info</div>";
                echo "<div>" . date("Y-m-d", $Fetch[$i]["create_time"]) . "</div>";
                echo "</li>";
            }
            echo "</ul>";
        }

        break;
    case "hot":
        break;
}
