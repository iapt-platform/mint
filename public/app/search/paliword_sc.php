<?php
//全文搜索
require_once '../config.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/load_lang.php"; //语言文件
require_once "../public/function.php";
require_once "../search/word_function.php";
require_once "../db/pali_text.php";

$_redis = redis_connect();
$_dbPaliText = new PaliText($_redis);

_load_book_index();

$op = $_GET["op"];
$word = mb_strtolower($_GET["key"], 'UTF-8');
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

$result = array();
$result["error"] = "";
$_start = microtime(true);
$result["time"][] = array("event" => "begin", "time" => $_start);

$_pagesize = 20;
if (isset($_GET["page"])) {
    $_page = (int) $_GET["page"];
} else {
    $_page = 0;
}

if (count($arrWordList) > 1) {
	# 查询多个词
	$out_data = array();
    /*
    PDO_Connect(_FILE_DB_PALITEXT_);
    # 首先精确匹配
    $words = implode(" ", $arrWordList);
    $query = "SELECT book,paragraph, text as content FROM "._TABLE_PALI_TEXT_." WHERE text like ?  LIMIT ? OFFSET ?";
    $Fetch1 = PDO_FetchAll($query, array("%{$words}%", $_pagesize, $_page * $_pagesize));
    
*/
	
	#postgresql full text search
    $dns = _DB_ENGIN_.":host="._DB_HOST_.";port="._DB_PORT_.";dbname="._DB_NAME_.";user="._DB_USERNAME_.";password="._DB_PASSWORD_.";";
    PDO_Connect($dns,_DB_USERNAME_,_DB_PASSWORD_);

    $query = "SELECT
    ts_rank('{0.1, 0.2, 0.4, 1}',
        full_text_search_weighted,
        websearch_to_tsquery('pali', ?)) +
    ts_rank('{0.1, 0.2, 0.4, 1}',
        full_text_search_weighted_unaccent,
        websearch_to_tsquery('pali_unaccent', ?))
    AS rank,
    ts_headline('pali', content,
                 websearch_to_tsquery('pali', ?),
                 'StartSel = <highlight>, StopSel = </highlight>,MaxWords=3500, MinWords=3500,HighlightAll=TRUE')
    AS highlight,
    book,paragraph,content 
    FROM fts_texts
    WHERE
        full_text_search_weighted
        @@ websearch_to_tsquery('pali', ?) OR
        full_text_search_weighted_unaccent
        @@ websearch_to_tsquery('pali_unaccent', ?)
    ORDER BY rank DESC
    LIMIT 40;";
    $Fetch1 = PDO_FetchAll($query, array($word, $word, $word, $word, $word));
	
    foreach ($Fetch1 as $key => $value) {
        # code...
        $newRecode["title"] = $_dbPaliText->getTitle($value["book"], $value["paragraph"]);
        $newRecode["path"] = _get_para_path($value["book"], $value["paragraph"]);
        $newRecode["book"] = $value["book"];
        $newRecode["para"] = $value["paragraph"];
        $newRecode["palitext"] = $value["content"];
		if(isset($value["highlight"])){
			$newRecode["highlight"] = $value["highlight"];
		}
        $newRecode["keyword"] = $arrWordList;
		if(isset($value["rank"])){
			$newRecode["wt"] = $value["rank"];
		}else{
			$newRecode["wt"] = 1;
		}
        
        $out_data[] = $newRecode;
    }
	$result["time"][] = array("event" => "fts精确匹配结束", "time" => microtime(true)-$_start);

	$result["data"] = $out_data;
    echo json_encode($result, JSON_UNESCAPED_UNICODE);
    # 然后查特别不精确的
    exit;
}

//计算某词在三藏中出现的次数
$time_start = microtime_float();
$arrRealWordList = countWordInPali($word);
$countWord = count($arrRealWordList);
$result["time"][] = array("event" => "计算某词在三藏中出现的次数", "time" => microtime(true) - $_start);
if ($countWord == 0) {
    #没查到 模糊查询

    PDO_Connect(_FILE_DB_PALITEXT_);
    $query = "SELECT book,paragraph, text FROM "._TABLE_PALI_TEXT_." WHERE text like ?  LIMIT ? OFFSET ?";
    $Fetch = PDO_FetchAll($query, array("%{$word}%", $_pagesize, $_page * $_pagesize));

    $result["data"] = $Fetch;
    exit;
}
$strQueryWordId = "("; //实际出现的单词id查询字串
$aQueryWordList = array(); //id 为键 拼写为值的数组
$aInputWordList = array(); //id 为键 拼写为值的数组 该词是否被选择
$aShowWordList = array(); //拼写为键 个数为值的数组
$aShowWordIdList = array(); //拼写为键 值Id的数组
$arrQueryId=array();
for ($i = 0; $i < $countWord; $i++) {
    $value = $arrRealWordList[$i];
    $strQueryWordId .= "'{$value["id"]}',";
	$arrQueryId[] = $value["id"];
    $aQueryWordList["{$value["id"]}"] = $value["word"];
    $aInputWordList["{$value["id"]}"] = false;
    $aShowWordList[$value["word"]] = $value["count"];
    $aShowWordIdList[$value["word"]] = $value["id"];
}

if (isset($_GET["words"])) {
    $word_selected = json_decode($_GET["words"]);
    if (count($word_selected) > 0) {
        $strQueryWordId = "(";
        foreach ($word_selected as $key => $value) {
            $strQueryWordId .= "'{$value}',";
            $aInputWordList["{$value}"] = true;
			$arrQueryId[] = $value;
        }
    }
}

$strQueryWordId = mb_substr($strQueryWordId, 0, mb_strlen($strQueryWordId, "UTF-8") - 1, "UTF-8");
$strQueryWordId .= ")";

$queryTime = (microtime_float() - $time_start) * 1000;

//显示单词列表
arsort($aShowWordList);
$result["time"][] = array("event" => "单词列表排序结束", "time" => microtime(true) - $_start);
$out_case = array();
$word_count = 0;
foreach ($aShowWordList as $x => $x_value) {
    $caseword = array();
    $caseword["id"] = $aShowWordIdList[$x];
    $caseword["spell"] = $x;
    $caseword["count"] = $x_value;
    $caseword["selected"] = $aInputWordList["{$aShowWordIdList[$x]}"];
    $word_count += $x_value;
    $out_case[] = $caseword;
}
$result["case"] = $out_case;
$result["case_num"] = $countWord;
$result["case_count"] = $word_count;

//查找这些词出现在哪些书中

$booklist = get_new_book_list($strQueryWordId);

$result["book_list"] = $booklist;
$result["book_tag"] = get_book_tag($strQueryWordId);
$result["time"][] = array("event" => "查找书结束", "time" => microtime(true) - $_start);

$wordInBookCounter = 0;
$strFirstBookList = "(";
foreach ($booklist as $onebook) {
    $wordInBookCounter += $onebook["count"];
    $strFirstBookList .= "'" . $onebook["book"] . "',";
    if ($wordInBookCounter >= 20) {
        break;
    }
}
$strFirstBookList = mb_substr($strFirstBookList, 0, mb_strlen($strFirstBookList, "UTF-8") - 1, "UTF-8");
$strFirstBookList .= ")";

$strQueryBookId = " ";
if (isset($_GET["book"])) {
    $book_selected = json_decode($_GET["book"]);
    $bookSelected = array();
    if (count($book_selected) > 0) {
        $strQueryBookId = " AND book IN (";
        foreach ($book_selected as $key => $value) {
            $strQueryBookId .= "'{$value}',";
            $bookSelected[$value] = 1;
        }
        $strQueryBookId = mb_substr($strQueryBookId, 0, mb_strlen($strQueryBookId, "UTF-8") - 1, "UTF-8");
        $strQueryBookId .= ")";

        foreach ($result["book_list"] as $bookindex => $bookvalue) {
            # code...
            $bookid = $bookvalue["book"];
            if (isset($bookSelected["{$bookid}"])) {
                $result["book_list"][$bookindex]["selected"] = true;
            } else {
                $result["book_list"][$bookindex]["selected"] = false;
            }
        }
    }
}
$result["time"][] = array("event" => "准备查询", "time" => microtime(true) - $_start);
//前20条记录
$time_start = microtime_float();

PDO_Connect(_FILE_DB_PALI_INDEX_);

$query = "SELECT count(*) from (SELECT book FROM "._TABLE_WORD_." WHERE \"wordindex\" in $strQueryWordId  $strQueryBookId group by book,paragraph) as qr  ";
$result["record_count"] = PDO_FetchOne($query);
$result["time"][] = array("event" => "查询记录数", "time" => microtime(true) - $_start);

$query = "SELECT book,paragraph, sum(weight) as wt FROM "._TABLE_WORD_." WHERE \"wordindex\" in $strQueryWordId $strQueryBookId GROUP BY book,paragraph ORDER BY wt DESC LIMIT ? OFFSET ?";
$Fetch = PDO_FetchAll($query,array($_pagesize , $_page * $_pagesize));
$result["time"][] = array("event" => "查询结束", "time" => microtime(true) - $_start);
$out_data = array();

$queryTime = (microtime_float() - $time_start) * 1000;
$iFetch = count($Fetch);
if ($iFetch > 0) {

    PDO_Connect(_FILE_DB_PALITEXT_);
    for ($i = 0; $i < $iFetch; $i++) {
        $newRecode = array();
		$paliword = array();
		foreach ($arrQueryId as $value) {
			# code...
			$paliword[] = $aQueryWordList["{$value}"];
		}
        $book = $Fetch[$i]["book"];
        $paragraph = $Fetch[$i]["paragraph"];
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
            $newRecode["title"] = $sFirstParentTitle;
            $newRecode["path"] = $path;
            $newRecode["book"] = $book;
            $newRecode["para"] = $paragraph;
            $newRecode["palitext"] = $FetchPaliText[0]["html"];
            $newRecode["keyword"] = $paliword;
            $newRecode["wt"] = $Fetch[$i]["wt"];
            $out_data[] = $newRecode;
        }

    }
}
$result["time"][] = array("event" => "查询路径结束", "time" => microtime(true) - $_start);
$result["data"] = $out_data;
echo json_encode($result, JSON_UNESCAPED_UNICODE);
