<?php
//全文搜索
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/config.php";
require_once "../public/_pdo.php";
require_once "../public/load_lang.php"; //语言文件
require_once "../public/function.php";
require_once "../config.php";

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

function render_book_list($strWordlist, $booklist = null)
{
    //查找这些词出现在哪些书中
    $arrBookType = json_decode(file_get_contents("../public/book_name/booktype.json"));
    PDO_Connect(_FILE_DB_BOOK_WORD_);
    if (isset($booklist)) {
        foreach ($booklist as $oneBook) {
            $aInputBook["{$oneBook}"] = 1;
        }
    }
    $query = "SELECT book,sum(count) as co from "._TABLE_BOOK_WORD_." where \"wordindex\" in $strWordlist group by book order by co DESC";
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
            PDO_Connect(_FILE_DB_WORD_INDEX_,_DB_USERNAME_,_DB_PASSWORD_);

            if (count($arrWordList) > 1) {
                echo "<div>";
                foreach ($arrWordList as $oneword) {
                    echo $oneword . "+";
                }
                echo "</div>";
            }
            echo "<div>";
            $query = "SELECT word,count from "._TABLE_WORD_INDEX_." where \"word_en\" like " . $PDO->quote($searching . '%') . " OR \"word\" like " . $PDO->quote($searching . '%') . " limit 50";
            echo $query;
            $Fetch = PDO_FetchAll($query);
            $queryTime = (microtime_float() - $time_start) * 1000;
            echo "<div >搜索时间：$queryTime </div>";
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
        }
    case "search":
        {
            if (count($arrWordList) > 1) {
                $strQuery = "";
                foreach ($arrWordList as $oneword) {
                    $strQuery .= "\"text\" like \"% {$oneword} %\" AND";
                }
                $strQuery = substr($strQuery, 0, -3);
                PDO_Connect(_FILE_DB_PALITEXT_);
                $query = "SELECT book,paragraph, html FROM "._TABLE_PALI_TEXT_." WHERE {$strQuery}  LIMIT 20";
                $Fetch = PDO_FetchAll($query);
                echo "<div>$query</div>";
                $iFetch = count($Fetch);
                foreach ($Fetch as $row) {
                    $html = $row["html"];
                    foreach ($arrWordList as $oneword) {
                        $html = str_replace($oneword, "<hl>{$oneword}</hl>", $html);
                    }
                    echo "<div class='dict_word'>{$html}</div>";
                }
                return;
            }
            $strDictTab = "<li id=\"dt_dict\" class=\"act\" onclick=\"tab_click('dict_ref','dt_dict')\">字典</li>";

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

            //查找实际出现的拼写

            $time_start = microtime_float();
            PDO_Connect(_FILE_DB_WORD_INDEX_,_DB_USERNAME_,_DB_PASSWORD_);
            $query = "SELECT id,word,count from "._TABLE_WORD_INDEX_." where \"word\" in  $strQueryWord";
            $arrRealWordList = PDO_FetchAll($query);
            $countWord = count($arrRealWordList);
            if ($countWord == 0) {
                echo "<p>没有查到。可能是拼写有问题。</p>";
                exit;
            }

            echo "<div id=\"dict_bold\">";
            //主显示区开始
            echo "<div style='display:flex;'>";

            //主显示区左侧开始
            echo "<div style='flex:3;max-width: 17em;min-width: 10em;'>";
            echo "<button onclick=\"dict_update_bold(0)\">筛选</button>";

            echo "<div>共{$countWord}单词符合</div>";
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

            $queryTime = (microtime_float() - $time_start) * 1000;

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
            echo "<div id='bold_book_list'>";
            $booklist = render_book_list($strQueryWordId);
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
            PDO_Connect(_FILE_DB_PALI_INDEX_);
            $query = "SELECT book,paragraph, wordindex FROM "._TABLE_WORD_." WHERE \"wordindex\" in $strQueryWordId and book in $strFirstBookList group by book,paragraph LIMIT 20";
            $Fetch = PDO_FetchAll($query);
            //echo "<div>$query</div>";
            $queryTime = (microtime_float() - $time_start) * 1000;
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                PDO_Connect(_FILE_DB_PALITEXT_);
                for ($i = 0; $i < $iFetch; $i++) {
                    $paliwordid = $Fetch[$i]["wordindex"];
                    $paliword = $aQueryWordList["{$paliwordid}"];
                    $book = $Fetch[$i]["book"];
                    $paragraph = $Fetch[$i]["paragraph"];
                    $bookInfo = _get_book_info($book);
                    $bookname = $bookInfo->title;
                    $c1 = $bookInfo->c1;
                    $c2 = $bookInfo->c2;
                    $c3 = $bookInfo->c3;

                    echo "<div class='dict_word'>";
                    echo "<div class='mean'><b>$paliword</b></div><br/>";
                    $path_1 = $c1 . ">";
                    if ($c2 !== "") {
                        $path_1 = $path_1 . $c2 . ">";
                    }
                    if ($c3 !== "") {
                        $path_1 = $path_1 . $c3 . ">";
                    }
                    $path_1 = $path_1 . "《{$bookname}》>";
                    $query = "SELECT * from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 1";
                    $FetchPaliText = PDO_FetchAll($query,array($book,$paragraph));
                    $countPaliText = count($FetchPaliText);
                    if ($countPaliText > 0) {
                        $path = "";
                        $parent = $FetchPaliText[0]["parent"];
                        $deep = 0;
                        $sFirstParentTitle = "";
                        //循环查找父标题 得到整条路径
                        while ($parent > -1) {
                            $query = "SELECT * from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 1";
                            $FetParent = PDO_FetchAll($query,array($book,$parent));
                            $path = "{$FetParent[0]["toc"]}>{$path}";
                            if ($sFirstParentTitle == "") {
                                $sFirstParentTitle = $FetParent[0]["toc"];
                            }
                            $parent = $FetParent[0]["parent"];
                            $deep++;
                            if ($deep > 5) {
                                break;
                            }
                        }
                        $path = $path_1 . $path . "para. " . $paragraph;
                        echo "<div class='mean'>$path</div>";

                        for ($iPali = 0; $iPali < $countPaliText; $iPali++) {
                            if (substr($paliword, -1) == "n") {
                                $paliword = substr($paliword, 0, -1);
                            }
                            $htmltext = $FetchPaliText[0]["html"];
                            $light_text = str_replace($paliword, "<hl>{$paliword}</hl>", $htmltext);
                            echo "<div class='wizard_par_div'>{$light_text}</div>";
                        }
                        //echo  "<div class='wizard_par_div'>{$light_text}</div>";
                        echo "<div class='search_para_tools'><button onclick=\"search_edit_now('{$book}','{$paragraph}','{$sFirstParentTitle}')\">Edit</button></div>";

                    }

                    echo "</div>";
                }
            }
            $queryTime = (microtime_float() - $time_start) * 1000;
            echo "<div >搜索时间：$queryTime </div>";
            echo "</div>";
            //黑体字主显示区右侧结束
            echo "</div>";
            //黑体字主显示区结束
            echo "</div>";
            //查黑体字结束

            echo "<div id='real_dict_tab'>$strDictTab</div>";
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

                        $query = "select * from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 20";
                        $FetchPaliText = PDO_FetchAll($query,array($book,$paragraph));
                        $countPaliText = count($FetchPaliText);
                        if ($countPaliText > 0) {
                            for ($iPali = 0; $iPali < $countPaliText; $iPali++) {
                                $path = "";
                                $parent = $FetchPaliText[0]["parent"];
                                $deep = 0;
                                $sFirstParentTitle = "";
                                while ($parent > -1) {
                                    $query = "select * from "._TABLE_PALI_TEXT_." where book = ? and paragraph = ? limit 1";
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
                                echo "<div class='search_para_tools'><button onclick=\"search_edit_now('{$book}','{$paragraph}','{$sFirstParentTitle}')\">Edit</button></div>";

                            }
                        }

                        echo "</div>";
                    }
                }
                break;
        }
        break;
}
