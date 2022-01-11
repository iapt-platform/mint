<?php
//全文搜索
require_once '../config.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/load_lang.php"; //语言文件
require_once "../public/function.php";

_load_book_index();

$op = $_GET["op"];
$word = mb_strtolower($_GET["word"], 'UTF-8');
$org_word = $word;
$arrWordList = str_getcsv($word, " ");

$count_return = 0;
$dict_list = array();

global $PDO;
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

function render_book_list($strWord, $booklist = null)
{
    //查找这些词出现在哪些书中
    $arrBookType = json_decode(file_get_contents("../public/book_name/booktype.json"));
    PDO_Connect(_FILE_DB_RESRES_INDEX_);
    if (isset($booklist)) {
        foreach ($booklist as $oneBook) {
            $aInputBook["{$oneBook}"] = 1;
        }
    }
    $query = "SELECT book,count(book) as co FROM \""._TABLE_RES_INDEX_."\" where title_en like '%{$strWord}%' or title like '%{$strWord}%' group by book order by co DESC";
    $Fetch = PDO_FetchAll($query);
    $iFetch = count($Fetch);
    $newBookList = array();
    if ($iFetch > 0) {
        $booktypesum["vinaya"] = array(0, 0);
        $booktypesum["sutta"] = array(0, 0);
        $booktypesum["abhidhamma"] = array(0, 0);
        $booktypesum["anna"] = array(0, 0);
        $booktypesum["mula"] = array(0, 0);
        $booktypesum["atthakattha"] = array(0, 0);
        $booktypesum["tika"] = array(0, 0);
        $booktypesum["anna2"] = array(0, 0);
        for ($i = 0; $i < $iFetch; $i++) {
            $book = $Fetch[$i]["book"];
            $sum = $Fetch[$i]["co"];
            array_push($newBookList, array($book, $sum));
            $t1 = $arrBookType[$book - 1]->c1;
            $t2 = $arrBookType[$book - 1]->c2;
            if (isset($booktypesum[$t1])) {
                $booktypesum[$t1][0]++;
                $booktypesum[$t1][1] += $sum;
            } else {
                $booktypesum[$t1][0] = 1;
                $booktypesum[$t1][1] = $sum;
            }
            if (isset($booktypesum[$t2])) {
                $booktypesum[$t2][0]++;
                $booktypesum[$t2][1] += $sum;
            } else {
                $booktypesum[$t2][0] = 1;
                $booktypesum[$t2][1] = $sum;
            }
        }
        echo "<div id='bold_book_list_new'>";
        echo "出现在{$iFetch}本书中：<br />";
        echo "<input id='bold_all_book' type='checkbox' checked onclick=\"dict_bold_book_all_select()\" />全选<br />";
        echo "<input id='id_book_filter_vinaya' type='checkbox' checked onclick=\"search_book_filter('id_book_filter_vinaya','vinaya')\" />律藏-{$booktypesum["vinaya"][0]}-{$booktypesum["vinaya"][1]}<br />";
        echo "<input id='id_book_filter_sutta'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_sutta','sutta')\" />经藏-{$booktypesum["sutta"][0]}-{$booktypesum["sutta"][1]}<br />";
        echo "<input id='id_book_filter_abhidhamma'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_abhidhamma','abhidhamma')\" />阿毗达摩藏-{$booktypesum["abhidhamma"][0]}-{$booktypesum["abhidhamma"][1]}<br />";
        echo "<input id='id_book_filter_anna'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_anna','anna')\" />其他-{$booktypesum["anna"][0]}-{$booktypesum["anna"][1]}<br /><br />";
        echo "<input id='id_book_filter_mula' type='checkbox' checked onclick=\"search_book_filter('id_book_filter_mula','mula')\" />根本-{$booktypesum["mula"][0]}-{$booktypesum["mula"][1]}<br />";
        echo "<input id='id_book_filter_atthakattha'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_atthakattha','atthakattha')\" />义注-{$booktypesum["atthakattha"][0]}-{$booktypesum["atthakattha"][1]}<br />";
        echo "<input id='id_book_filter_tika'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_tika','tika')\" />复注-{$booktypesum["tika"][0]}-{$booktypesum["tika"][1]}<br />";
        echo "<input id='id_book_filter_anna2'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_anna2','anna2')\" />其他-{$booktypesum["anna2"][0]}-{$booktypesum["anna2"][1]}<br /><br />";
        echo "<div style='margin:1em 0 ;'></div>";
        for ($i = 0; $i < $iFetch; $i++) {

            $book = $Fetch[$i]["book"];
            $bookname = _get_book_info($book)->title;
            if (isset($booklist)) {
                if (isset($aInputBook["{$book}"])) {
                    $bookcheck = "checked";
                } else {
                    $bookcheck = "";
                }
            } else {
                $bookcheck = "checked";
            }
            $t1 = $arrBookType[$book - 1]->c1;
            $t2 = $arrBookType[$book - 1]->c2;
            echo "<div class='{$t1}'>";
            echo "<div class='{$t2}'>";
            echo "<input id='bold_book_{$i}' type='checkbox' $bookcheck value='{$book}'/>";
            echo "<a onclick=\"dict_bold_book_select({$i})\">";
            echo "《{$bookname}》({$Fetch[$i]["co"]})<br />";
            echo "</a>";
            echo "</div></div>";
        }
        echo "<input id='bold_book_count' type='hidden' value='{$iFetch}' />";
        echo "</div>";
    }

    return ($newBookList);
    //查找这些词出现在哪些书中结束

}

switch ($op) {
    case "pre": //预查询
        {
            $time_start = microtime_float();

            $searching = $arrWordList[count($arrWordList) - 1];

            PDO_Connect( _FILE_DB_RESRES_INDEX_);

            if (count($arrWordList) > 1) {
                echo "<div>";
                foreach ($arrWordList as $oneword) {
                    echo $oneword . "+";
                }
                echo "</div>";
            }
            $query = "SELECT title from \""._TABLE_RES_INDEX_."\" where (title_en like " . $PDO->quote("%" . $searching . '%') . " OR title like " . $PDO->quote("%" . $searching . '%') . ") group by title limit 20";
            $Fetch = PDO_FetchAll($query);
            $queryTime = (microtime_float() - $time_start) * 1000;
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                for ($i = 0; $i < $iFetch; $i++) {
                    $word = $Fetch[$i]["title"];
                    echo "<a href='title.php?key={$word}'>$word</a>";
                }
            }
            break;
        }
    case "search":
        {

            $time_start = microtime_float();
            echo "<div id=\"dict_bold\">";
            //主显示区开始
            echo "<div style='display:flex;'>";

            //主显示区左侧开始
            echo "<div style='flex:3;max-width: 17em;min-width: 10em;'>";
            echo "<button onclick=\"dict_update_bold(0)\">筛选</button>";
            $queryTime = (microtime_float() - $time_start) * 1000;

            echo "<div ></div>";
            //查找这些词出现在哪些书中
            echo "<div id='bold_book_list'>";
            $booklist = render_book_list($_GET["word"]);
            echo "</div>";

            $wordInBookCounter = 0;
            $strFirstBookList = "(";
            foreach ($booklist as $onebook) {
                $wordInBookCounter += $onebook[1];
                $strFirstBookList .= "'" . $onebook[0] . "',";
                if ($wordInBookCounter >= 20) {
                    break;
                }
            }
            $strFirstBookList = mb_substr($strFirstBookList, 0, mb_strlen($strFirstBookList, "UTF-8") - 1, "UTF-8");
            $strFirstBookList .= ")";

            echo "</div>";
            //黑体字主显示区左侧结束

            //黑体字主显示区右侧开始
            echo "<div id=\"dict_bold_right\" style='flex:7;'>";
            //前20条记录
            $time_start = microtime_float();

            PDO_Connect( _FILE_DB_RESRES_INDEX_);
            if (isset($_GET["booklist"])) {
                $query = "SELECT * from \""._TABLE_RES_INDEX_."\" where (title_en like " . $PDO->quote("%" . $_GET["word"] . '%') . " OR title like " . $PDO->quote("%" . $_GET["word"] . '%') . ") and book in {$_GET["booklist"]} limit 50";
            } else {
                $query = "SELECT * from \""._TABLE_RES_INDEX_."\" where title_en like " . $PDO->quote("%" . $_GET["word"] . '%') . " OR title like " . $PDO->quote("%" . $_GET["word"] . '%') . " limit 50";
            }
            $Fetch = PDO_FetchAll($query);
            $queryTime = (microtime_float() - $time_start) * 1000;
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                PDO_Connect( _FILE_DB_PALITEXT_);
                for ($i = 0; $i < $iFetch; $i++) {
                    $title = $Fetch[$i]["title"];
                    $book = $Fetch[$i]["book"];
                    $paragraph = $Fetch[$i]["paragraph"];
                    echo "<div style='margin: 10px 0;padding: 5px;border-bottom: 1px solid var(--border-line-color);'>";
                    echo "<div style='font-size: 130%;font-weight: 700;'><a href='../reader/?view=chapter&book={$book}&para={$paragraph}&display=para' target='_blank'>$title</a></div>";

                    $bookInfo = _get_book_info($book);
                    $bookname = $bookInfo->title;
                    $c1 = $bookInfo->c1;
                    $c2 = $bookInfo->c2;
                    $c3 = $bookInfo->c3;

                    $path_1 = $c1 . ">";
                    if ($c2 !== "") {
                        $path_1 = $path_1 . $c2 . ">";
                    }
                    if ($c3 !== "") {
                        $path_1 = $path_1 . $c3 . ">";
                    }
                    $path_1 = $path_1 . "《{$bookname}》>";

                    $path = _get_para_path($book, $paragraph);
                    echo $path_1 . $path;

                    $query = "select chapter_len from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 1";
                    $chapter_len = PDO_FetchAll($query,array($book,$paragraph));
                    $chapter_len = $chapter_len[0]["chapter_len"];

                    $query = "select text from "._TABLE_PALI_TEXT_." where book = ? and (paragraph > ? and paragraph <= ?) limit 3";
                    $FetchPaliText = PDO_FetchAll($query,array($book,$paragraph,($paragraph + $chapter_len)));
                    $paliContent = "";
                    foreach ($FetchPaliText as $text) {
                        $paliContent .= $text["text"];
                    }
                    $paliContent = mb_substr($paliContent, 0, 200, "UTF-8");
                    echo "<div>{$paliContent}</div>";

                    echo "<div class='search_para_tools'></div>";

                    echo "</div>";
                }
            }
            $queryTime = (microtime_float() - $time_start) * 1000;
            echo "<div >搜索时间：$queryTime </div>";
            echo "</div>";
            //黑体字主显示区右侧结束
            echo "<div id=\"dict_bold_review\" style='flex:2;'>";

            echo "</div>";

            echo "</div>";
            //黑体字主显示区结束
            echo "</div>";
            //查黑体字结束

            echo "<div id='real_dict_tab'></div>";
            break;
        }
    case "update":
        $target = $_GET["target"];
        switch ($target) {
            case "bold";
                $wordlist = $_GET["wordlist"];
                $booklist = $_GET["booklist"];
                $aBookList = ltrim($booklist, "(");
                $aBookList = rtrim($aBookList, ")");
                $aBookList = str_replace("'", "", $aBookList);
                $aBookList = str_getcsv($aBookList);
                $arrBookType = json_decode(file_get_contents("../public/book_name/booktype.json"));
                //查找这些词出现在哪些书中
                $newBookList = render_book_list($wordlist, $aBookList);

                //前20条记录
                $time_start = microtime_float();

                PDO_Connect(_FILE_DB_PALI_INDEX_);

                $query = "SELECT * from "._TABLE_WORD_." where \"wordindex\" in $wordlist and \"book\" in $booklist group by book,paragraph  limit 20";
                $Fetch = PDO_FetchAll($query);
                //echo "<div>$query</div>";
                $queryTime = (microtime_float() - $time_start) * 1000;
                echo "<div >搜索时间：$queryTime </div>";
                $iFetch = count($Fetch);
                if ($iFetch > 0) {
                    PDO_Connect(_FILE_DB_PALITEXT_);
                    for ($i = 0; $i < $iFetch; $i++) {
                        $paliword = $Fetch[$i]["wordindex"];
                        //$paliword=$wordlist["{$paliwordid}"];

                        $book = $Fetch[$i]["book"];
                        $bookInfo = _get_book_info($book);
                        $bookname = $bookInfo->title;
                        $c1 = $bookInfo->c1;
                        $c2 = $bookInfo->c2;
                        $c3 = $bookInfo->c3;
                        $paragraph = $Fetch[$i]["paragraph"];

                        $path_1 = $c1 . ">";
                        if ($c2 !== "") {
                            $path_1 = $path_1 . $c2 . ">";
                        }
                        if ($c3 !== "") {
                            $path_1 = $path_1 . $c3 . ">";
                        }
                        $path_1 = $path_1 . "《{$bookname}》>";

                        echo "<div class='dict_word'>";
                        echo "<div class='dict'>《{$bookname}》 $c1 $c2 </div>";
                        echo "<div class='mean'>$paliword</div>";

                        $query = "SELECT * from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 20";
                        $FetchPaliText = PDO_FetchAll($query,array($book,$paragraph));
                        $countPaliText = count($FetchPaliText);
                        if ($countPaliText > 0) {
                            for ($iPali = 0; $iPali < $countPaliText; $iPali++) {
                                $path = "";
                                $parent = $FetchPaliText[0]["parent"];
                                $deep = 0;
                                $sFirstParentTitle = "";
                                while ($parent > -1) {
                                    $query = "SELECT * from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 1";
                                    $FetParent = PDO_FetchAll($query,array($book,$parent));
                                    if ($sFirstParentTitle == "") {
                                        $sFirstParentTitle = $FetParent[0]["toc"];
                                    }
                                    $path = "{$FetParent[0]["toc"]}>{$path}";
                                    $parent = $FetParent[0]["parent"];
                                    $deep++;
                                    if ($deep > 5) {
                                        break;
                                    }
                                }
                                $path = $path_1 . $path . "No. " . $paragraph;

                                echo "<div class='mean'>$path</div>";
                                //echo  "<div class='mean'>$paliword</div>";

                                if (substr($paliword, -1) == "n") {
                                    $paliword = substr($paliword, 0, -1);
                                }
                                $light_text = str_replace($paliword, "<hl>{$paliword}</hl>", $FetchPaliText[$iPali]["html"]);
                                echo "<div class='wizard_par_div'>{$light_text}</div>";
                                echo "<div class='search_para_tools'></div>";

                            }
                        }

                        echo "</div>";
                    }
                }
                break;
        }
        break;
}
