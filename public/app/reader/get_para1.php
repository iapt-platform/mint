<?php
require_once "../config.php";
require_once "../log/pref_log.php";
require_once "../public/_pdo.php";
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
if (isset($_GET["par"])) {
    $_para = $_GET["par"];
} else {
    $_para = 0;
}

if (isset($_GET["start"])) {
    $_start = $_GET["start"];
}
if (isset($_GET["begin"])) {
    $_start = $_GET["begin"];
}
if (isset($_GET["end"])) {
    $_end = $_GET["end"];
}
if (isset($_GET["channel"])) {
    $_channel = $_GET["channel"];
}

$_view = $_GET["view"];

$output["toc"] = array();
$output["sentences"] = array();
$output["head"] = 0;

$output["title"]="";
$output["subtitle"]="";
$output["summary"]="";
$output["content"]="";
$output["owner"]="";
$output["username"]=array("username"=>"","nickname"=>"");
$output["status"]="";
$output["debug"]='';


$dns = _FILE_DB_PALI_TOC_;
$dbh_toc = new PDO($dns, _DB_USERNAME_, _DB_PASSWORD_, array(PDO::ATTR_PERSISTENT => true));
$dbh_toc->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

if ($_view == "sent") {
    $output["content"] = "{{". $_book . "-" . $_para . "-". $_start . "-" . $_end . "}}";
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}
if ($_view == "simsent" || $_view == "sim") {
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
				$sent_list = $mPaliSent->getInfo($value["id"]);
				$output["content"] .= "{{". $sent_list["book"] . "-" . $sent_list["paragraph"] . "-". $sent_list["begin"] . "-" . $sent_list["end"] . "}}";
			}
			echo json_encode($output, JSON_UNESCAPED_UNICODE);
		}
	}
	exit;
}

$paraBegin=0;
$paraEnd=0;

PDO_Connect(_FILE_DB_PALITEXT_);
$query = "SELECT level , parent, chapter_len,chapter_strlen FROM "._TABLE_PALI_TEXT_."  WHERE book= ? AND paragraph= ?  order by paragraph asc";
$FetchParInfo = PDO_FetchRow($query, array($_book, $_para));
if ($FetchParInfo) {
    switch ($_view) {
        case 'chapter':
            # code...

			if($FetchParInfo["level"]>0 && $FetchParInfo["level"]<8){
				//是标题
				$paraBegin = $_para;
            	$paraEnd = $_para + $FetchParInfo["chapter_len"] - 1;
			}
            else{
				#不是标题，加载所在段落
				$paraBegin = $FetchParInfo["parent"];
				$query = "SELECT  level , parent, chapter_len,chapter_strlen FROM "._TABLE_PALI_TEXT_."  WHERE book= ? AND paragraph= ?  order by paragraph asc";
				$FetchParInfo = PDO_FetchRow($query, array($_book, $paraBegin));
            	$paraEnd = $paraBegin + $FetchParInfo["chapter_len"] - 1;
			}
            break;
        case 'para':
            $paraBegin = $_para;
            $paraEnd = $_para;
            # code...
            break;
        default:
            # code...
            $paraBegin = $_para;
            $paraEnd = $_para;
            break;
    }

    //获取下级目录
    $query = "SELECT level,paragraph,toc FROM "._TABLE_PALI_TEXT_."  WHERE book= ? AND (paragraph BETWEEN ? AND ? ) AND level < 8 order by paragraph asc";
    $toc = PDO_FetchAll($query, array($_book, $paraBegin, $paraEnd));
	if(count($toc)>0){
		$output["title"] = $toc[0]["toc"];
	}

    /*
    目录
    */
	if(isset($_channel)){
		$firstChannel = explode(',',$_channel)[0];
	}
    $sTocOutput = "\n\n";
	if(count($toc)>1){
		$currLevel = $toc[0]["level"];
		$ulLevel = 0;
        $minLevel = 8;
        foreach ($toc as $key => $value) {
            if($value["level"] < $minLevel ){
                $minLevel = $value["level"];
            }
        }
		foreach ($toc as $key => $value) {
			# code...
            if(empty($value["toc"])){
                $sToc = "unnamed";
            }else{
                $sToc = $value["toc"];
            }
            $sToc = str_replace(['[',']'],[' [','] '],$sToc);
			if($value["level"] > $currLevel  ){
				$ulLevel++;
			}
			else if($value["level"] < $currLevel ){
				$ulLevel--;
			}
			$currLevel = $value["level"];
            $space = "";
			for ($i=$minLevel; $i < $currLevel; $i++) {
				# code...
				$space .= "  ";
			}
			//目录进度
			$progress = "";
			$urlChannel = "";
			if(isset($firstChannel)){
				$query = "SELECT title , progress FROM "._TABLE_PROGRESS_CHAPTER_."  WHERE book= ? AND para= ?  AND channel_id=?";
				$sth_title = $dbh_toc->prepare($query);
				$sth_title->execute(array($_book, $value["paragraph"], $firstChannel));
				$trans_title = $sth_title->fetch(PDO::FETCH_ASSOC);
				if ($trans_title) {
					if(!empty($trans_title['title'])){
						$sToc = $trans_title['title'];
					}
					$progress = "＜".sprintf('%d',$trans_title['progress']*100)."%";
				}
				$urlChannel = "&channel={$_channel}";
			}
			$sToc = str_replace("[","",$sToc);
			$sToc = str_replace("]","",$sToc);
			$sTocOutput .= $space . "- [{$sToc}](../article/index.php?view=chapter&book={$_book}&par={$value["paragraph"]}{$urlChannel}){$progress}\n";
		}
	}
	$sTocOutput .= "\n\n";

	$output["debug"] .= "chapter_strlen:{$FetchParInfo["chapter_strlen"]}\n";
    if ($FetchParInfo["chapter_strlen"] > _MAX_CHAPTER_LEN_ && $_view === "chapter" && count($toc) > 1) {
        #文档过大，只加载目录
		$output["debug"] .= "文档过大，只加载目录\n";
		if ($toc[1]["paragraph"] - $_para > 1) {
            # 最高级目录和下一个目录中间有正文层级的段落间隔
			$output["debug"] .= "最高级目录和下一个目录中间有正文层级的段落间隔\n";
            $paraBegin = $_para;
            $paraEnd = $toc[1]["paragraph"] - 1;
            $output["head"] = 1;
        } else {
            #中间无间隔
			$output["debug"] .= "最高级目录和下一个目录中间无正文层级的段落间隔\n";
            $output["content"] .= $sTocOutput;
            echo json_encode($output, JSON_UNESCAPED_UNICODE);
            exit;
        }
    }

    PDO_Connect(_FILE_DB_PALI_SENTENCE_);

    $query = "SELECT book,paragraph, word_begin as begin, word_end as end FROM "._TABLE_PALI_SENT_." WHERE book= ? AND (paragraph BETWEEN ? AND ? )  order by paragraph,word_begin asc";
    $sent_list = PDO_FetchAll($query, array($_book, $paraBegin, $paraEnd));
	$iCurrPara=0;
	$output["sent_list"] = $sent_list;
	foreach ($sent_list as $key => $value) {
		# code...
		if($value["paragraph"]!==$iCurrPara){
			$output["content"] .= "\n\n";
			$iCurrPara = $value["paragraph"];

			if($_view=="chapter" && $paraBegin != $_para){
				if($_para==$value["paragraph"]){
					$output["content"] .= "<div id='para_focus' class='focus'>\n\n";
				}
			}
			if($_view=="chapter" && $paraBegin!=$_para){
				if($_para==$value["paragraph"]-1){
					$output["content"] .= "\n\n</div>";
				}
			}
			$output["content"] .= "<div class='page_number' page='{$iCurrPara}'>{$iCurrPara}</div>\n\n";
		}

		$output["content"] .= "{{". $value["book"] . "-" . $value["paragraph"] . "-". $value["begin"] . "-" . $value["end"] . "}}";

	}
    $output["content"] .= $sTocOutput;
    echo json_encode($output, JSON_UNESCAPED_UNICODE);

} else {
    echo json_encode($output, JSON_UNESCAPED_UNICODE);

}
PrefLog();
