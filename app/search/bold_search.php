<?php
//查询参考字典
require_once '../config.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../public/load_lang.php";

_load_book_index();

$op = $_GET["op"];
$word = mb_strtolower($_GET["word"], 'UTF-8');
$org_word = $word;

$count_return = 0;

global $PDO;

switch ($op) {
    case "pre": //预查询
        PDO_Connect(_FILE_DB_REF_INDEX_);
        echo "<div>";
        $query = "SELECT word,count from dict where \"eword\" like " . $PDO->quote($word . '%') . " OR \"word\" like " . $PDO->quote($word . '%') . "  limit 20";

        $Fetch = PDO_FetchAll($query);
        $iFetch = count($Fetch);
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $word = $Fetch[$i]["word"];
                $count = $Fetch[$i]["count"];
                echo "<div class='dict_word_list'>";
                echo "<a href='bold.php?key={$word}'>$word-$count</a>";
                echo "</div>";
            }
        }
        echo "</div>";
        break;
    case "search":

        $strDictTab = "<li id=\"dt_dict\" class=\"act\" onclick=\"tab_click('dict_ref','dt_dict')\">{$_local->gui->dict}</li>";

        //查黑体字开始
        $arrBookName = json_decode(file_get_contents("../public/book_name/sc.json"));
        echo "<div id=\"dict_bold\" style=''>";

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

        PDO_Connect(_FILE_DB_BOLD_);
        //查询符合的记录数
        $query = "SELECT count(*) as co from "._TABLE_WORD_BOLD_." where \"word2\" in  $strQueryWord";
        $Fetch = PDO_FetchOne($query);
        if ($Fetch > 0) {
            $strDictTab .= "<li id=\"dt_bold\"  onclick=\"tab_click('dict_bold','dt_bold')\">{$_local->gui->vannana}({$Fetch})</li>";

            echo "{$_local->gui->search}：$word {$_local->gui->find_about}{$Fetch}{$_local->gui->result}<br />";

            //黑体字主显示区开始
            echo "<div class='dict_bold_main'>";

            //黑体字主显示区左侧开始
            echo "<div class='dict_bold_left'>";
            echo "<button onclick=\"dict_update_bold(0)\">" . $_local->gui->filter . "</button>";
            /*查找实际出现的拼写
            $strQueryWord中是所有可能的拼写
             */
            $realQueryWord = "(";
            $query = "SELECT word2,count(word) as co from "._TABLE_WORD_BOLD_." where \"word2\" in $strQueryWord group by word2 order by co DESC";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo "<div>";
                echo "<input id='bold_all_word' type='checkbox' checked='true' value='' onclick=\"dict_bold_word_all_select()\"/>{$_local->gui->all_select}<br />";
                for ($i = 0; $i < $iFetch; $i++) {
                    $realQueryWord .= "'{$Fetch[$i]["word2"]}',";
                    echo "<input id='bold_word_{$i}' type='checkbox' checked value='{$Fetch[$i]["word2"]}' />";
                    echo "<a onclick=\"dict_bold_word_select({$i})\">";
                    echo $Fetch[$i]["word2"] . ":" . $Fetch[$i]["co"] . "{$_local->gui->times}<br />";
                    echo "</a>";
                }
                $realQueryWord = mb_substr($realQueryWord, 0, mb_strlen($realQueryWord, "UTF-8") - 1, "UTF-8");
                $realQueryWord .= ")";
                echo "<input id='bold_word_count' type='hidden' value='{$iFetch}' />";
                echo "</div>";

            }

            //查找这些词出现在哪些书中
            $query = "SELECT book,count(word) as co from "._TABLE_WORD_BOLD_." where \"word2\" in $realQueryWord group by book order by co DESC";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                echo "<div id='bold_book_list'>";
                echo "{$_local->gui->presented_in}{$iFetch}{$_local->gui->book}：<br />";
                echo "<input type='checkbox' checked='true' value='' />{$_local->gui->all_select}<br />";
                for ($i = 0; $i < $iFetch; $i++) {
                    $book = $Fetch[$i]["book"];
                    $bookname = _get_book_info($book)->title;
                    echo "<input id='bold_book_{$i}' type='checkbox' checked value='{$book}'/>";
                    echo "<a onclick=\"dict_bold_book_select({$i})\">";
                    echo "《{$bookname}》:{$Fetch[$i]["co"]}{$_local->gui->times}<br />";
                    echo "</a>";
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
            $query = "SELECT * from "._TABLE_WORD_BOLD_." where \"word2\" in $realQueryWord limit 20";
            $Fetch = PDO_FetchAll($query);
            $iFetch = count($Fetch);
            if ($iFetch > 0) {
                PDO_Connect(_FILE_DB_PALITEXT_);
                for ($i = 0; $i < $iFetch; $i++) {
                    $paliword = $Fetch[$i]["word"];
                    $book = $Fetch[$i]["book"];
                    $bookInfo = _get_book_info($book);
                    $bookname = $bookInfo->title;
                    $bookPath = $bookInfo->c1 . ">" . $bookInfo->c2 . ">" . $bookInfo->c3;
                    $paragraph = $Fetch[$i]["paragraph"];
                    $base = $Fetch[$i]["base"];
                    $pali = $Fetch[$i]["pali"];
                    echo "<div class='dict_word'>";
                    echo "<div class='book' ><span style='font-size:110%;font-weight:700;'>《{$bookname}》</span> <tag>$bookInfo->c1</tag> <tag>$bookInfo->c2</tag> </div>";
                    echo "<div style='font-size: 130%;font-weight: 700;'>$paliword</div>";

                    if (strlen($pali) > 1) {
                        echo "<div class='mean'>$pali</div>";
                    } else {
                        //PDO_Connect(_FILE_DB_PALITEXT_);
                        $query = "SELECT * from "._TABLE_PALI_TEXT_." where \"book\" = '{$book}' and \"paragraph\" = '{$paragraph}' limit 20";
                        $FetchPaliText = PDO_FetchAll($query);
                        $countPaliText = count($FetchPaliText);
                        if ($countPaliText > 0) {

                            for ($iPali = 0; $iPali < $countPaliText; $iPali++) {
                                $path = "";
                                $parent = $FetchPaliText[0]["parent"];
                                $deep = 0;
                                $sFirstParentTitle = "";
                                while ($parent > -1) {
                                    $query = "SELECT * from pali_text where \"book\" = '{$book}' and \"paragraph\" = '{$parent}' limit 1";
                                    $FetParent = PDO_FetchAll($query);
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
                                $path = $bookPath . $path . "No. " . $paragraph;
                                echo "<div class='mean' style='font-size:120%;'><a href='../reader/?view=para&book={$book}&para={$paragraph}&display=para' target='_blank'>$path</a></div>";
                                if (substr($paliword, -1) == "n") {
                                    $paliword = substr($paliword, 0, -1);
                                }
                                $htmlPara = str_replace(".0", "。0", $FetchPaliText[$iPali]["html"]);
                                $aSent = str_getcsv($htmlPara, ".");

                                $aSentInfo = array();
                                $aBold = array();
                                echo "<div class='wizard_par_div'>";
                                foreach ($aSent as $sent) {
                                    //array_push($aSentInfo,false);
                                    //array_push($aBold,false);

                                    if (stristr($sent, $paliword)) {
                                        echo "<span>{$sent}.</span><br>";
                                        //    $aSent[$i]=str_replace($paliword,"<hl>{$paliword}</hl>",$aSent[$i]);
                                        //$aSentInfo[$i]=true;
                                    }
                                    //if(stristr($aSent[$i],"bld")){
                                    //$aBold[$i]=true;
                                    //}
                                }
                                echo "</div>";
                                /*
                                $output="";
                                $bold_on=false;
                                for($i=0;$i<count($aSent);$i++){
                                if($aBold[$i]){
                                if($aSentInfo[$i]){
                                $output.=$aSent[$i]."<br>";
                                $bold_on=true;
                                }
                                else{
                                echo "<div>{$output}</div>";
                                $output="";
                                $bold_on=false;
                                }
                                }
                                else{
                                if($bold_on){
                                echo "<div>{$aBold[$i]}</div>";
                                }
                                }
                                }
                                 */
                                //$light_text=str_replace($paliword,"<hl>{$paliword}</hl>",$FetchPaliText[$iPali]["vri_text"]);
                                //$light_text=str_replace(".",".<br />",$light_text);
                                //echo  "<div class='wizard_par_div'>{$light_text}</div>";
                            }
                        }
                    }
                    echo "<div class='search_para_tools'></div>";
                    echo "</div>";
                }
            }
            echo "</div>";
            //黑体字主显示区右侧结束
            echo "</div>";
            //黑体字主显示区结束
        }
        echo "</div>";
        //查黑体字结束
        break;
    case "update":
        $target = $_GET["target"];
        switch ($target) {
            case "bold";
                $arrBookName = json_decode(file_get_contents("../public/book_name/sc.json"));
                PDO_Connect(_FILE_DB_BOLD_);
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
                $query = "SELECT book,count(word) as co from "._TABLE_WORD_BOLD_." where \"word2\" in $wordlist group by book order by co DESC";
                $Fetch = PDO_FetchAll($query);
                $iFetch = count($Fetch);
                if ($iFetch > 0) {
                    echo "<div id='bold_book_list_new'>";
                    echo "出现在{$iFetch}本书中：<br />";
                    echo "<input id='bold_all_book' type='checkbox' checked onclick=\"dict_bold_book_all_select()\" />全选<br />";
                    for ($i = 0; $i < $iFetch; $i++) {
                        $book = $Fetch[$i]["book"];
                        $bookname = $arrBookName[$book - 1]->title;
                        if (isset($aInputBook["{$book}"])) {
                            $bookcheck = "checked";
                        } else {
                            $bookcheck = "";
                        }
                        echo "<input id='bold_book_{$i}' type='checkbox' $bookcheck value='{$book}'/>";
                        echo "<a onclick=\"dict_bold_book_select({$i})\">";
                        echo "《{$bookname}》({$Fetch[$i]["co"]})<br />";
                        echo "</a>";
                    }
                    echo "<input id='bold_book_count' type='hidden' value='{$iFetch}' />";
                    echo "</div>";
                }
                //查找这些词出现在哪些书中结束
                //前20条记录
                if ($booklist == "()") {
                    echo "<div>请选择书名</div>";
                }
                $query = "SELECT * from "._TABLE_WORD_BOLD_." where \"word2\" in $wordlist and \"book\" in $booklist  limit 20";
                $Fetch = PDO_FetchAll($query);
                $iFetch = count($Fetch);
                if ($iFetch > 0) {
                    for ($i = 0; $i < $iFetch; $i++) {
                        $paliword = $Fetch[$i]["word"];
                        $book = $Fetch[$i]["book"];
                        $bookname = $arrBookName[$book - 1]->title;
                        $c1 = $arrBookName[$book - 1]->c1;
                        $c2 = $arrBookName[$book - 1]->c2;
                        $bookPath = "$c1>$c2";
                        $paragraph = $Fetch[$i]["paragraph"];
                        $base = $Fetch[$i]["base"];
                        $pali = $Fetch[$i]["pali"];
                        echo "<div class='dict_word'>";
                        echo "<div class='book' ><span style='font-size:110%;font-weight:700;'>《{$bookname}》</span> <tag>$c1</tag> <tag>$2</tag> </div>";

                        echo "<div class='mean'>$paliword</div>";

                        if (strlen($pali) > 1) {
                            echo "<div class='mean'>$pali</div>";
                        } else {
                            PDO_Connect(_FILE_DB_PALITEXT_);
                            $query = "SELECT * from "._TABLE_PALI_TEXT_." where \"book\" = '{$book}' and \"paragraph\" = '{$paragraph}' limit 20";
                            $FetchPaliText = PDO_FetchAll($query);
                            $countPaliText = count($FetchPaliText);
                            if ($countPaliText > 0) {
                                for ($iPali = 0; $iPali < $countPaliText; $iPali++) {

                                    $path = "";
                                    $parent = $FetchPaliText[0]["parent"];
                                    $deep = 0;
                                    $sFirstParentTitle = "";
                                    while ($parent > -1) {
                                        $query = "SELECT * from "._TABLE_PALI_TEXT_." where \"book\" = '{$book}' and \"paragraph\" = '{$parent}' limit 1";
                                        $FetParent = PDO_FetchAll($query);
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
                                    $path = "<span>{$bookPath}>{$path} No. {$paragraph}</span>";
                                    //echo  "<div class='mean'>$path</div>";
                                    echo "<div class='mean' style='font-size:120%;'><a href='../reader/?view=para&book={$book}&para={$paragraph}&display=para' target='_blank'>$path</a></div>";

                                    if (substr($paliword, -1) == "n") {
                                        $paliword = substr($paliword, 0, -1);
                                    }
                                    $light_text = str_replace($paliword, "<hl>{$paliword}</hl>", $FetchPaliText[$iPali]["html"]);
                                    $light_text = str_replace(".", ".<br /><br />", $light_text);
                                    echo "<div class='wizard_par_div'>{$light_text}</div>";
                                }
                            }
                        }
                        echo "<div class='search_para_tools'></div>";
                        echo "</div>";
                    }
                }
                break;
        }
        break;
}
