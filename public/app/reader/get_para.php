<?php
require_once "../public/_pdo.php";
require_once "../config.php";
require_once "../redis/function.php";
require_once "../db/pali_sent.php";
require_once "../db/pali_sim_sent.php";

$mRedis = redis_connect();

define("_MAX_CHAPTER_LEN_", 20000);

if (isset($_GET["book"])) {
    $_book = $_GET["book"];
} else {
    $_book = 0;
}
if (isset($_GET["para"])) {
    $_para = $_GET["para"];
} else {
    $_para = 0;
}

if (isset($_GET["begin"])) {
    $_begin = $_GET["begin"];
}
if (isset($_GET["end"])) {
    $_end = $_GET["end"];
}
$_view = $_GET["view"];

$output["toc"] = array();
$output["sentences"] = array();
$output["head"] = 0;

if ($_view == "sent") {
    $output["sentences"] = array(array("book" => $_book, "paragraph" => $_para, "begin" => $_begin, "end" => $_end));
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}
if ($_view == "sim") {
	$mPaliSent = new PaliSentence($mRedis);
	$mPaliSim = new PaliSimSentence($mRedis);
	if(!isset($_GET["id"])){
		$id = $mPaliSent->getId($_book,$_para,$_begin,$_end);
	}
	else{
		$id = $_GET["id"];
	}
	{
		$sent_list[] = $mPaliSent->getInfo($id);
		$arrList = $mPaliSim->getSimById($id);
		if($arrList){
			foreach ($arrList as $key => $value) {
				# code...
				$sent_list[] = $mPaliSent->getInfo($value["id"]);
			}
			$output["sentences"] = $sent_list;
			echo json_encode($output, JSON_UNESCAPED_UNICODE);
		}
	}
	exit;
}

$paraBegin=0;
$paraEnd=0;

PDO_Connect(_FILE_DB_PALITEXT_);
$query = "SELECT level , parent, chapter_len,chapter_strlen FROM "._TABLE_PALI_TEXT_."  WHERE book= ? AND paragraph= ?";
$FetchParInfo = PDO_FetchRow($query, array($_book, $_para));
if ($FetchParInfo) {
    switch ($_view) {
        case 'chapter':
            # code...

			if($FetchParInfo["level"]>0 && $FetchParInfo["level"]<8){
				$paraBegin = $_para;
            	$paraEnd = $_para + $FetchParInfo["chapter_len"] - 1;
			}
            else{
				$paraBegin = $FetchParInfo["parent"];
				$query = "SELECT  level , parent, chapter_len,chapter_strlen FROM "._TABLE_PALI_TEXT_."  WHERE book= ? AND paragraph= ?";
				$FetchParInfo = PDO_FetchRow($query, array($_book, $paraBegin));
            	$paraEnd = $paraBegin + $FetchParInfo["chapter_len"] - 1;
			}
            break;
        case 'para':
            $paraBegin = $_para;
            $paraEnd = $_para;
            # code...
            break;
		case "sim":

			break;
        default:
            # code...
            $paraBegin = $_para;
            $paraEnd = $_para;
            break;
    }

    //获取下级目录
    $query = "SELECT level,paragraph,toc FROM "._TABLE_PALI_TEXT_."  WHERE book= ? AND (paragraph BETWEEN ?AND ? ) AND level < 8 ";
    $output["toc"] = PDO_FetchAll($query, array($_book, $paraBegin, $paraEnd));

    if ($FetchParInfo["chapter_strlen"] > _MAX_CHAPTER_LEN_ && $_view === "chapter" && count($output["toc"]) > 1) {
        if ($output["toc"][1]["paragraph"] - $_para > 1) {
            # 中间有间隔
            $paraBegin = $_para;
            $paraEnd = $output["toc"][1]["paragraph"] - 1;
            $output["head"] = 1;
        } else {
            #中间无间隔
            echo json_encode($output, JSON_UNESCAPED_UNICODE);
            exit;
        }

    }

    PDO_Connect(_FILE_DB_PALI_SENTENCE_);

    $query = "SELECT book,paragraph,word_begin as begin, word_end as end FROM "._TABLE_PALI_SENT_." WHERE book= ? AND (paragraph BETWEEN ?AND ? ) ";
    $sent_list = PDO_FetchAll($query, array($_book, $paraBegin, $paraEnd));
    $output["sentences"] = $sent_list;
    echo json_encode($output, JSON_UNESCAPED_UNICODE);

} else {
    echo json_encode($output, JSON_UNESCAPED_UNICODE);

}
