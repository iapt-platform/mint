<?php
//查询参考字典
require '../public/casesuf.inc';
require '../public/union.inc';
include "../public/config.php";
include "../public/_pdo.php";
if (isset($_GET["language"])) {
    $currLanguage = $_GET["language"];
} else {
    if (isset($_COOKIE["language"])) {
        $currLanguage = $_COOKIE["language"];
    } else {
        $currLanguage = "en";
    }
}

if (file_exists($dir_language . $currLanguage . ".php")) {
    require $dir_language . $currLanguage . ".php";
    echo ("<script language=\"javascript\" src=\"language/$currLanguage.js\"></script>");
} else {
    include $dir_language . "default.php";
}

$op = $_GET["op"];
$word = mb_strtolower($_GET["word"], 'UTF-8');
$org_word = $word;

$count_return = 0;
$dict_list = array();

global $PDO;

switch ($op) {
    case "pre": //预查询
        PDO_Connect(_FILE_DB_WORD_INDEX_,_DB_USERNAME_,_DB_PASSWORD_);
        echo "<div>";
        $query = "SELECT word,count from "._TABLE_WORD_INDEX_." where \"word_en\" like " . $PDO->quote($word . '%') . " OR \"word\" like " . $PDO->quote($word . '%') . " limit 50";
        echo $query;
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $word = $Fetch[$i]["word"];
                $count = $Fetch[$i]["count"];
                echo "<div class='dict_word_list'>";
                echo "<a onclick='dict_pre_word_click(\"$word\")'>$word-$count</a>";
                echo "</div>";
            }
        }
        echo "</div>";
        break;
    case "search":

        $strDictTab = "<li id=\"dt_dict\" class=\"act\" onclick=\"tab_click('dict_ref','dt_dict')\">字典</li>";

        //获取书名
        $arrBookName = json_decode(file_get_contents("../public/book_name/sc.json"));
        echo "<div id=\"dict_bold\">";

        //加语尾
        $arrNewWord = array();
        for ($row = 0; $row < count($case); $row++) {
            $len = mb_strlen($case[$row][0], "UTF-8");
            $end = mb_substr($word, 0 - $len, null, "UTF-8");
            if ($end == $case[$row][0]) {
                $newWord = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $len, "UTF-8") . $case[$row][1];
                $arrNewWord[$newWord] = 1;
            }
        }
        //加连读词尾
        $arrUnWord = array();
        for ($row = 0; $row < count($union); $row++) {
            $len = mb_strlen($union[$row][0], "UTF-8");
            foreach ($arrNewWord as $x => $x_value) {
                $end = mb_substr($x, 0 - $len, null, "UTF-8");
                if ($end == $union[$row][0]) {
                    $newWord = mb_substr($x, 0, mb_strlen($x, "UTF-8") - $len, "UTF-8") . $union[$row][1];
                    $arrUnWord[$newWord] = 1;
                }
            }
        }
        //将连读词和$arrNewWord混合
        foreach ($arrUnWord as $x => $x_value) {
            $arrNewWord[$x] = 1;
        }
        if (count($arrNewWord) > 0) {
            $strQueryWord = "(";
            foreach ($arrNewWord as $x => $x_value) {
                $strQueryWord .= "'{$x}',";
            }
            $strQueryWord = mb_substr($strQueryWord, 0, mb_strlen($strQueryWord, "UTF-8") - 1, "UTF-8");
            $strQueryWord .= ")";
        } else {
            $strQueryWord = "('{$word}')";
        }

        //主显示区开始
        echo "<div style='display:flex;'>";

        //主显示区左侧开始
        echo "<div style='flex:3;max-width: 17em;min-width: 10em;'>";
        echo "<button onclick=\"dict_update_bold(0)\">" . $module_gui_str['search']['1001'] . "</button>";
        /*查找实际出现的拼写

         */
        PDO_Connect(_FILE_DB_WORD_INDEX_,_DB_USERNAME_,_DB_PASSWORD_);
        $query = "SELECT id,word,count from "._TABLE_WORD_INDEX_." where \"word\" in  $strQueryWord";
        $arrRealWordList = PDO_FetchAll($query);
        $countWord = count($arrRealWordList);
        echo "$word<br />共{$countWord}单词符合<br />";
        $strQueryWordId = "("; //实际出现的单词id查询字串
        $aQueryWordList = array(); //id 为键 拼写为值的数组
        $aShowWordList = array(); //拼写为键 个数为值的数组
        $aShowWordIdList = array(); //拼写为键 值Id的数组
        for ($i = 0; $i < $countWord; $i++) {
            $value = $arrRealWordList[$i];
            $strQueryWordId .= "'{$value["id"]}',";
            $aQueryWordList["{$value["id"]}"] = $value["word"];
            $aShowWordList[$value["word"]] = $value["count"];
            $aShowWordIdList[$value["word"]] = $value["id"];
        }
        $strQueryWordId = mb_substr($strQueryWordId, 0, mb_strlen($strQueryWordId, "UTF-8") - 1, "UTF-8");
        $strQueryWordId .= ")";

        //显示单词列表
        echo "<div>";
        echo "<input id='bold_all_word' type='checkbox' checked='true' value='' onclick=\"dict_bold_word_all_select()\"/>全选<br />";
        arsort($aShowWordList);
        $i = 0;
        foreach ($aShowWordList as $x => $x_value) {
            $wordid = $aShowWordIdList[$x];
            echo "<input id='bold_word_{$i}' type='checkbox' checked value='{$wordid}' />";
            echo "<a onclick=\"dict_bold_word_select({$i})\">";
            echo $x . ":" . $x_value . "<br />";
            echo "</a>";
            $i++;
        }

        echo "<input id='bold_word_count' type='hidden' value='{$countWord}' />";
        echo "</div>";

        //查找这些词出现在哪些书中
        PDO_Connect(_FILE_DB_BOOK_WORD_);
        $query = "SELECT book,sum(count) as co FROM "._TABLE_BOOK_WORD_." WHERE "._TABLE_WORD_INDEX_." IN $strQueryWordId  GROUP BY book LIMIT 217";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            $aWordInBook = array();
            foreach ($Fetch as $xBook) {
                $aWordInBook["{$xBook["book"]}"] = $xBook["co"];
            }
            arsort($aWordInBook);
            echo "<div id='bold_book_list'>";
            echo "出现在{$iFetch}本书中：<br />";
            echo "<input type='checkbox' checked='true' value='' />全选<br />";
            $i = 0;
            foreach ($aWordInBook as $x => $x_value) {
                $book = $x;
                $bookname = $arrBookName[$book - 1]->title;
                echo "<input id='bold_book_{$i}' type='checkbox' checked value='{$book}'/>";
                echo "<a onclick=\"dict_bold_book_select({$i})\">";
                echo "《{$bookname}》:{$x_value}次<br />";
                echo "</a>";
                $i++;
            }
            echo "<input id='bold_book_count' type='hidden' value='{$iFetch}' />";
            echo "</div>";
        }
        //查找这些词出现在哪些书中结束

        echo "</div>";
        //黑体字主显示区左侧结束

        //黑体字主显示区右侧开始
        echo "<div id=\"dict_bold_right\" style='flex:7;'>";
        //前20条记录

        PDO_Connect(_FILE_DB_WORD_INDEX_,_DB_USERNAME_,_DB_PASSWORD_);

        $query = "SELECT book,paragraph, wordindex FROM "._TABLE_WORD_." WHERE \"wordindex\" in $strQueryWordId LIMIT 20";
        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            PDO_Connect(_FILE_DB_PALITEXT_);
            for ($i = 0; $i < $iFetch; $i++) {
                $paliwordid = $Fetch[$i]["wordindex"];
                $paliword = $aQueryWordList["{$paliwordid}"];
                $book = $Fetch[$i]["book"];
                $paragraph = $Fetch[$i]["paragraph"];
                $bookname = $arrBookName[$book - 1]->title;
                $c1 = $arrBookName[$book - 1]->c1;

                echo "<div class='dict_word'>";
                echo "<div class='dict'>《{$bookname}》 $c1</div>";
                echo "<div class='mean'>$paliword</div>";

                {
                    $query = "select * from vri_text where \"book\" = '{$book}' and \"paragraph\" = '{$paragraph}' limit 1";
                    $FetchPaliText = PDO_FetchAll($query);
                    $countPaliText = count($FetchPaliText);
                    if ($countPaliText > 0) {
                        for ($iPali = 0; $iPali < $countPaliText; $iPali++) {
                            if (substr($paliword, -1) == "n") {
                                $paliword = substr($paliword, 0, -1);
                            }
                            $light_text = str_replace($paliword, "<hl>{$paliword}</hl>", $FetchPaliText[$iPali]["vri_text"]);
                            echo "<div class='wizard_par_div'>{$light_text}</div>";
                        }
                    }
                }
                echo "</div>";
            }
        }
        echo "</div>";
        //黑体字主显示区右侧结束
        echo "</div>";
        //黑体字主显示区结束
        echo "</div>";
        //查黑体字结束

        echo "<div id='real_dict_tab'>$strDictTab</div>";
        break;
    case "update":
        $target = $_GET["target"];
        switch ($target) {
            case "bold";
                $arrBookName = json_decode(file_get_contents("../public/book_name/sc.json"));
                $arrBookType = json_decode(file_get_contents("../public/book_name/booktype.json"));
                PDO_Connect(_FILE_DB_BOOK_WORD_);
                $wordlist = $_GET["wordlist"];
                $booklist = $_GET["booklist"];
                $aBookList = ltrim($booklist, "(");
                $aBookList = rtrim($aBookList, ")");
                $aBookList = str_replace("'", "", $aBookList);
                $aBookList = str_getcsv($aBookList);
                foreach ($aBookList as $oneBook) {
                    $aInputBook["{$oneBook}"] = 1;
                }

                //查找这些词出现在哪些书中
                $query = "SELECT book,sum(count) as co from "._TABLE_BOOK_WORD_." where "._TABLE_WORD_INDEX_." in $wordlist group by book order by co DESC";
                $Fetch = PDO_FetchAll($query);
                $iFetch = count($Fetch);
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
                    for ($i = 0; $i < $iFetch; $i++) {

                        $book = $Fetch[$i]["book"];
                        $bookname = $arrBookName[$book - 1]->title;
                        if (isset($aInputBook["{$book}"])) {
                            $bookcheck = "checked";
                        } else {
                            $bookcheck = "";
                        }
                        $t1 = $arrBookType[$book - 1]->c1;
                        $t2 = $arrBookType[$book - 1]->c2;
                        echo "<div class='{$t1}'>";
                        echo "<div class='{$t2}'>";
                        echo "<input id='bold_book_{$i}' type='checkbox' $bookcheck value='{$book}'/>";
                        echo "<a onclick=\"dict_bold_book_select({$i})\">";
                        echo "《{$bookname}》:{$Fetch[$i]["co"]}次<br />";
                        echo "</a>";
                        echo "</div></div>";
                    }
                    echo "<input id='bold_book_count' type='hidden' value='{$iFetch}' />";
                    echo "</div>";
                }
                //查找这些词出现在哪些书中结束
                //前20条记录
                PDO_Connect(_FILE_DB_WORD_INDEX_,_DB_USERNAME_,_DB_PASSWORD_);

                $query = "SELECT * from "._TABLE_WORD_." where \"wordindex\" in $wordlist and \"book\" in $booklist group by book,paragraph  limit 20";
                $Fetch = PDO_FetchAll($query);
                $iFetch = count($Fetch);
                if ($iFetch > 0) {
                    PDO_Connect(_FILE_DB_PALITEXT_);
                    for ($i = 0; $i < $iFetch; $i++) {
                        $paliword = $Fetch[$i]["wordindex"];

                        $book = $Fetch[$i]["book"];
                        $bookname = $arrBookName[$book - 1]->title;
                        $c1 = $arrBookName[$book - 1]->c1;
                        $c2 = $arrBookName[$book - 1]->c2;
                        $paragraph = $Fetch[$i]["paragraph"];
                        echo "<div class='dict_word'>";
                        echo "<div class='dict'>《{$bookname}》 $c1 $c2 </div>";
                        echo "<div class='mean'>$paliword</div>";

                        $query = "select * from vri_text where \"book\" = '{$book}' and \"paragraph\" = '{$paragraph}' limit 20";
                        $FetchPaliText = PDO_FetchAll($query);
                        $countPaliText = count($FetchPaliText);
                        if ($countPaliText > 0) {
                            for ($iPali = 0; $iPali < $countPaliText; $iPali++) {
                                if (substr($paliword, -1) == "n") {
                                    $paliword = substr($paliword, 0, -1);
                                }
                                $light_text = str_replace($paliword, "<hl>{$paliword}</hl>", $FetchPaliText[$iPali]["vri_text"]);
                                echo "<div class='wizard_par_div'>{$light_text}</div>";
                            }
                        }
                        echo "</div>";
                    }
                }
                break;
        }
        break;
}
