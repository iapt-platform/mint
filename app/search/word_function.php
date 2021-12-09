<?php
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/load_lang.php"; //语言文件
require_once "../public/function.php";

function get_new_book_list($strWordlist, $booklist = null)
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
            $title = _get_book_info($book)->title;
            $sum = $Fetch[$i]["co"];
            array_push($newBookList, array("book" => $book, "count" => $sum, "title" => $title));
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

    }

    return ($newBookList);
    //查找这些词出现在哪些书中结束
}

function get_book_tag($strWordlist, $booklist = null)
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

        $output = array();
        $output[] = array("tag" => "vinaya", "title" => "律藏", "count" => $booktypesum["vinaya"][0]);
        $output[] = array("tag" => "sutta", "title" => "经藏", "count" => $booktypesum["sutta"][0]);
        $output[] = array("tag" => "abhidhamma", "title" => "阿毗达摩藏", "count" => $booktypesum["abhidhamma"][0]);
        $output[] = array("tag" => "anna", "title" => "其他", "count" => $booktypesum["anna"][0]);
        $output[] = array("tag" => "mula", "title" => "根本", "count" => $booktypesum["mula"][0]);
        $output[] = array("tag" => "atthakattha", "title" => "义注", "count" => $booktypesum["atthakattha"][0]);
        $output[] = array("tag" => "tika", "title" => "复注", "count" => $booktypesum["tika"][0]);
        $output[] = array("tag" => "anna2", "title" => "其他", "count" => $booktypesum["anna2"][0]);
        return $output;
    }

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

        echo "<div id='bold_book_list_new' style='margin:1em;0'>";

        echo "<div>出现在{$iFetch}本书中：</div>";
        echo "<div>全选<input id='bold_all_book' type='checkbox' checked onclick=\"dict_bold_book_all_select()\" /></div>";
        echo "<div>律藏-{$booktypesum["vinaya"][0]}-{$booktypesum["vinaya"][1]}<input id='id_book_filter_vinaya' type='checkbox' checked onclick=\"search_book_filter('id_book_filter_vinaya','vinaya')\" /></div>";
        echo "<div>经藏-{$booktypesum["sutta"][0]}-{$booktypesum["sutta"][1]}<input id='id_book_filter_sutta'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_sutta','sutta')\" /></div>";
        echo "<div>阿毗达摩藏-{$booktypesum["abhidhamma"][0]}-{$booktypesum["abhidhamma"][1]}<input id='id_book_filter_abhidhamma'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_abhidhamma','abhidhamma')\" /></div>";
        echo "<div >其他-{$booktypesum["anna"][0]}-{$booktypesum["anna"][1]}<input id='id_book_filter_anna'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_anna','anna')\" /></div>";
        echo "<div style='margin-bottom:1em';></div>";
        echo "<div>根本-{$booktypesum["mula"][0]}-{$booktypesum["mula"][1]}<input id='id_book_filter_mula' type='checkbox' checked onclick=\"search_book_filter('id_book_filter_mula','mula')\" /></div>";
        echo "<div>义注-{$booktypesum["atthakattha"][0]}-{$booktypesum["atthakattha"][1]}<input id='id_book_filter_atthakattha'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_atthakattha','atthakattha')\" /></div>";
        echo "<div>复注-{$booktypesum["tika"][0]}-{$booktypesum["tika"][1]}<input id='id_book_filter_tika'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_tika','tika')\" /></div>";
        echo "<div>其他-{$booktypesum["anna2"][0]}-{$booktypesum["anna2"][1]}<input id='id_book_filter_anna2'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_anna2','anna2')\" /></div>";
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

function countWordInPali($word, $sort = false, $limit = 0)
{
    //加语尾
    $case = $GLOBALS['case'];
    $union = $GLOBALS['union'];
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
    for ($i = 0; $i < 2; $i++) {
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

    $dsn = _FILE_DB_WORD_INDEX_;
    $PDO = new PDO($dsn, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    if ($limit == 0) {
        $sSqlLimit = "";
    } else {
        $sSqlLimit = "limit " . $limit;
    }
    if ($sort) {
        $sSqlSort = "order by count DESC";
    } else {
        $sSqlSort = "";
    }

    $query = "SELECT id,word,count,bold,len from "._TABLE_WORD_INDEX_." where \"word\" in  $strQueryWord " . $sSqlSort . " " . $sSqlLimit;

    $stmt = $PDO->query($query);
    $arrRealWordList = $stmt->fetchAll(PDO::FETCH_ASSOC);

    return ($arrRealWordList);

}
