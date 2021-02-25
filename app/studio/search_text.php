<?php
//pali 正文搜索
require 'casesuf.inc';
require 'dict_find_un.inc';
include "./config.php";
include "./_pdo.php";
include "book_list_zh.inc";

if (isset($_GET["language"])) {
    $currLanguage = $_GET["language"];
    $_COOKIE["language"] = $currLanguage;
} else {
    if (isset($_COOKIE["language"])) {
        $currLanguage = $_COOKIE["language"];
    } else {
        $currLanguage = "en";
        $_COOKIE["language"] = $currLanguage;
    }
}

//load language file
if (file_exists($dir_language . $currLanguage . ".php")) {
    require $dir_language . $currLanguage . ".php";
} else {
    include $dir_language . "default.php";
}

if (isset($_GET["device"])) {
    $currDevice = $_GET["device"];
} else {
    if (isset($_COOKIE["device"])) {
        $currDevice = $_COOKIE["device"];
    } else {
        $currDevice = "computer";
    }
}

$op = $_GET["op"];
$word = mb_strtolower($_GET["word"], 'UTF-8');
$org_word = $word;

$count_return = 0;
$dict_list = array();

global $PDO;
$dictFileName = $dir_dict_system . "word_index.db";
PDO_Connect("$dictFileName");

switch ($op) {
    case "pre": //预查询
        $query = "select word,count as co from main_word_index where \"word_en\" like " . $PDO->quote($word . '%') . " OR \"word\" like " . $PDO->quote($word . '%') . " limit 0,100";
        //echo $query;
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        echo "<wordlist>";
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $outXml = "<word>";
                $word = $Fetch[$i]["word"];
                $outXml = $outXml . "<pali>$word</pali>";
                $outXml = $outXml . "<count>" . $Fetch[$i]["co"] . "</count>";
                $outXml = $outXml . "</word>";
                echo $outXml;
            }
        }
        echo "</wordlist>";
        break;
    case "search":
        //直接查询
        //先查书籍单词总表 获取书号
        $word_string = "'" . str_replace(",", "','", $word);
        $word_string = mb_substr($word_string, 0, -2, "UTF-8");

        $query = "select word,book,sum(count) as co from word_in_book where \"word\" in (" . $word_string . ") group by book";

        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        echo "<div id='search_result_index' style='flex:3;'>";
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $outXml = "<p>";
                $word = $Fetch[$i]["word"];
                //$outXml = $outXml."<pali>$word</pali>";
                $bookid = $Fetch[$i]["book"];
                $outXml = $outXml . "<book>" . $book[$bookid] . "</book>-";
                $outXml = $outXml . "<count>" . $Fetch[$i]["co"] . "</count>";
                $outXml = $outXml . "</p>";
                echo $outXml;
            }
        }
        echo "</div>";

        echo "<div class='search_pali_text_prev' style='flex:7;'>";
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                //遍历每一本书 获取段落号
                $bookid = $Fetch[$i]["book"];

                $db_file = "../appdata/palicanon/templet/" . $bookid . "_tpl.db3";
                //open database
                PDO_Connect("$db_file");
                echo "<div><div class='search_code'>input:$org_word<br>";
                $word_string = "\"real\" ='" . str_replace(",", "' or \"real\" = '", $org_word);
                //echo $word_string;
                $word_string = mb_substr($word_string, 0, -14, "UTF-8");
                $query_pali = "SELECT * FROM \"main\" WHERE " . $word_string . " group by paragraph";
                echo $query_pali . "<br />";

                $Fetch_pali = PDO_FetchAll($query_pali);
                $iFetch_pali = count($Fetch_pali);
                echo "book:$bookid paragraph:" . $iFetch_pali . "<br /></div>";
                //var_dump($Fetch_pali);

                if ($iFetch_pali > 0) {
                    $db_file = "../appdata/palicanon/pali_text/" . $bookid . "_pali.db3";
                    //open database
                    PDO_Connect("$db_file");

                    for ($j = 0; $j < $iFetch_pali; $j++) {
                        echo "<div class='search_code'>par:" . $Fetch_pali[$j]['paragraph'] . "<br>";
                        $query_pali_text = "SELECT * FROM \"data\" WHERE paragraph=" . $Fetch_pali[$j]['paragraph'];
                        $par = $Fetch_pali[$j]['paragraph'];
                        echo $query_pali_text . "<br />";

                        $Fetch_pali_text = PDO_FetchAll($query_pali_text);
                        $iFetch_pali_text = count($Fetch_pali_text);
                        echo $iFetch_pali_text . "<br /></div>";
                        //var_dump($Fetch_pali);

                        if ($iFetch_pali_text > 0) {
                            for ($k = 0; $k < $iFetch_pali_text; $k++) {
                                echo "<div class='paragraph'>";
                                echo "<div class='book_title'>";
                                echo "<div class='book_name' style='display:none'>" . $book[$bookid] . "</div>";
                                echo "<div id='book_path#" . $k . "' class='book_path'>" . $Fetch_pali_text[$k]['book'] . "#" . $module_gui_str['editor_palicannon']['1014'] . "&nbsp;" . $Fetch_pali_text[$k]['paragraph'] . "&nbsp;" . $module_gui_str['editor_project']['1043'] . "</div>";
                                echo "</div>";
                                $newText = str_replace($word, "<span class='height_light'>$word</span>", $Fetch_pali_text[$k]['vri_text']);
                                echo "<div class='par_text'>" . $newText . "</div>";
                                echo "<div class='toolbar'><button onclick='search_edit($bookid,$par)'>Edit</button></div>";
                                echo "</div>";
                            }
                        }
                        //break;
                    }
                }
                echo "</div>";
                /*
            //获取段落号结束

            $db_file = "../appdata/palicanon/pali_text/".$bookid."_pali.db3";
            //open database
            PDO_Connect("$db_file");
            $query_pali="SELECT * FROM \"data\" WHERE \"text\"  like '%".$word."%'";
            echo $org_word;
            $word_string="\"text\" like '%".str_replace(",","%' or \"text\" like '%",$org_word);
            echo $word_string;
            $word_string=mb_substr($word_string,0,-18,"UTF-8");
            $query_pali="SELECT * FROM \"data\" WHERE ".$word_string;
            echo $query_pali."<br />";

            $Fetch_pali = PDO_FetchAll($query_pali);
            $iFetch_pali=count($Fetch_pali);
            echo $iFetch_pali."<br />";
            //var_dump($Fetch_pali);

            if($iFetch_pali>0){
            for($j=0;$j<$iFetch_pali;$j++){
            echo "<div class='paragraph'>";
            echo "<div class='book_title'>";
            echo "<div class='book_name'>".$book[$bookid]."</div>";
            echo "<div class='book_path'>"."path>path ".$Fetch_pali[$j]['book']."第".$Fetch_pali[$j]['paragraph']."段"."</div>";
            echo "</div>";
            $newText=str_replace($word,"<span class='height_light'>$word</span>",$Fetch_pali[$j]['text']);
            echo "<div class='par_text'>".$newText."</div>";
            echo "</div>";
            }
            }

             */
            }
        }
        echo "</div>";

        break;

}
