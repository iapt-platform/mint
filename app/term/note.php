<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../channal/function.php";
require_once "../ucenter/function.php";
require_once "../usent/function.php";
require_once "../pali_text/function.php";
require_once "../redis/function.php";
require_once "../db/custom_book.php";

$redis = redis_connect();

$_channal = new Channal();
$_userinfo = new UserInfo();
$_sentPr = new SentPr($redis);
$_pali_text = new PaliText($redis);
$_pali_book = new PaliBook($redis);

$_data = array();
if (isset($_POST["data"])) {
    $_data = json_decode($_POST["data"], true);
} else {
    if (isset($_GET["id"])) {
        $id = $_GET["id"];
    } else {
        echo "error: no id";
        return;
    }
    if (isset($_GET["info"])) {
        $info = $_GET["info"];
    } else {
        echo "error: no info";
        return;
    }
    $_data[] = array("id" => $id, "data" => $info);
}

if (isset($_POST["setting"])) {
    $_setting = json_decode($_POST["setting"], true);
} else {
    $_setting["lang"] = "";
    $_setting["channal"] = "";
}

$dns = "" . _FILE_DB_PALI_SENTENCE_;
$db_pali_sent = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$db_pali_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = "" . _FILE_DB_PALI_SENTENCE_SIM_;
$db_pali_sent_sim = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$db_pali_sent_sim->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$dns = "" . _FILE_DB_SENTENCE_;
$db_trans_sent = new PDO($dns, "", "", array(PDO::ATTR_PERSISTENT => true));
$db_trans_sent->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$output = array();

#查询有阅读权限的channel
$channal_list = array();
if (isset($_COOKIE["user_uid"])) {
    PDO_Connect( _FILE_DB_CHANNAL_);
    $query = "SELECT id from channal where owner = ?   limit 0,100";
    $Fetch_my = PDO_FetchAll($query, array($_COOKIE["user_uid"]));
    foreach ($Fetch_my as $key => $value) {
        # code...
        $channal_list[] = $value["id"];
    }

    # 找协作的
    $Fetch_coop = array();
    $query = "SELECT channal_id FROM cooperation WHERE  user_id = ? ";
    $coop_channal = PDO_FetchAll($query, array($_COOKIE["user_uid"]));
    if (count($coop_channal) > 0) {
        foreach ($coop_channal as $key => $value) {
            # code...
            $channal_list[] = $value["channal_id"];
        }
    }
    /*  创建一个填充了和params相同数量占位符的字符串 */

}
if (count($channal_list) > 0) {
    $channel_place_holders = implode(',', array_fill(0, count($channal_list), '?'));
    $channel_query = " OR channal IN ($channel_place_holders)";
} else {
    $channel_query = "";
}

# 查询有阅读权限的channel 结束
$custom_book = new CustomBookSentence($redis);

foreach ($_data as $key => $value) {
    # code...
    $id = $value["id"];
    $arrInfo = explode("@", $value["data"]);
    if (isset($arrInfo[1])) {
        $sentChannal = $arrInfo[1];
    } else {
        $sentChannal = "";
    }
    if (isset($arrInfo[0])) {
        $arrSent = str_getcsv($arrInfo[0], "-");
        $bookId = $arrSent[0];
        $para = $arrSent[1];
        $begin = $arrSent[2];
        $end = $arrSent[3];
    } else {
        $bookId = 0;
        $para = 0;
        $begin = 0;
        $end = 0;
    }
	$pali_sim = 0;
    if ($redis != false) {
		if($bookId<1000){
			$result = $redis->hGet('pali://sent/' . $bookId . "_" . $para . "_" . $begin . "_" . $end,"pali");
			$palitext = $result;
			$pali_text_id = $redis->hGet('pali://sent/' . $bookId . "_" . $para . "_" . $begin . "_" . $end,"id");
			$pali_sim = $redis->hGet('pali://sent/' . $bookId . "_" . $para . "_" . $begin . "_" . $end,"sim_count");
		}
		else{
			$palitext = $custom_book->getText($bookId,$para,$begin,$end);
			$pali_text_id = 0;
		}

    } else {
        $query = "SELECT id,html FROM 'pali_sent' WHERE book = ? AND paragraph = ? AND begin = ? AND end = ? ";
        $sth = $db_pali_sent->prepare($query);
        $sth->execute(array($bookId, $para, $begin, $end));
        $row = $sth->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $palitext = $row['html'];
            $pali_text_id = $row['id'];
        } else {
            $palitext = "";
            $pali_text_id = 0;
		}
		//查询相似句


			$query = "SELECT count FROM 'sent_sim_index' WHERE sent_id = ? ";
			$sth = $db_pali_sent_sim->prepare($query);
			$sth->execute(array($pali_text_id));
			$row = $sth->fetch(PDO::FETCH_ASSOC);
			if ($row) {
				$pali_sim = $row["count"]; //explode(",",$row["sim_sents"]) ;
			}		
    }
    //find out translation 查询译文

    $tran = "";
    $translation = array();
    $tran_channal = array();
    try {
        if (empty($_setting["channal"])) {
            if ($sentChannal == "") {
                $query = "SELECT * FROM sentence WHERE book= ? AND paragraph= ? AND begin= ? AND end= ?  AND strlen >0 and (status = 30 {$channel_query} )   order by modify_time DESC limit 0 ,1 ";
                $channal = "";
            } else {
                $query = "SELECT * FROM sentence WHERE book= ? AND paragraph= ? AND begin= ? AND end= ?  AND strlen >0  AND channal = ?  limit 0 ,1 ";
            }
        } else {
            $query = "SELECT * FROM sentence WHERE book= ? AND paragraph= ? AND begin= ? AND end= ?  AND strlen >0  AND channal = ?  limit 0 ,1 ";
            $channal = $_setting["channal"];
        }

        $stmt = $db_trans_sent->prepare($query);
        if (empty($_setting["channal"])) {
			#没有指定channel
            if ($sentChannal == "") {
				#句子信息也没指定channel 查找默认译文
                $parm = array($bookId, $para, $begin, $end);
                $parm = array_merge_recursive($parm, $channal_list);
                $stmt->execute($parm);
                $Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($Fetch) {
                    $tran = $Fetch["text"];
                    $translation[] = array("id" => $Fetch["id"], 
										   "book"=>$bookId,
										   "para"=>$para,
										   "begin"=>$begin,
										   "end"=>$end,
										   "text" => $Fetch["text"], 
										   "lang" => $Fetch["language"], 
										   "channal" => $Fetch["channal"], 
										   "status" => $Fetch["status"], 
										   "editor" => $Fetch["editor"],
										   "editor_name"=>$_userinfo->getName($Fetch["editor"]),
										   "update_time"=>$Fetch["modify_time"]
										);
                    if (!empty($Fetch["channal"])) {
                        $tran_channal[$Fetch["channal"]] = $Fetch["channal"];
                    }
                }
            } else {
				#句子信息包含channel
				#{{book-para-begin-end@channelid}}
                $stmt->execute(array($bookId, $para, $begin, $end, $sentChannal));
                $Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($Fetch) {
                    $tran = $Fetch["text"];
                    $translation[] = array("id" => $Fetch["id"], 
											"book"=>$bookId,
											"para"=>$para,
											"begin"=>$begin,
											"end"=>$end,
										   "text" => $Fetch["text"], 
										   "lang" => $Fetch["language"], 
										   "channal" => $Fetch["channal"], 
										   "status" => $Fetch["status"], 
										   "editor" => $Fetch["editor"],
										   "editor_name"=>$_userinfo->getName($Fetch["editor"]),
										   "update_time"=>$Fetch["modify_time"]
										);
                    $tran_channal[$Fetch["channal"]] = $Fetch["channal"];
                }
            }
        } else {
			#指定了channel
            $arrChannal = explode(",", $_setting["channal"]);
            foreach ($arrChannal as $key => $value) {
                # code...
                $stmt->execute(array($bookId, $para, $begin, $end, $value));
                $Fetch = $stmt->fetch(PDO::FETCH_ASSOC);
                if ($Fetch) {
                    $translation[] = array("id" => $Fetch["id"],
										   "book"=>$bookId,
										   "para"=>$para,
										   "begin"=>$begin,
										   "end"=>$end,
										   "text" => $Fetch["text"], 
										   "lang" => $Fetch["language"], 
										   "channal" => $value, 
										   "status" => $Fetch["status"], 
										   "editor" => $Fetch["editor"],
										   "editor_name"=>$_userinfo->getName($Fetch["editor"]),
										   "update_time"=>$Fetch["modify_time"]
										);

                } else {
                    $translation[] = array("id" => "", 
											"book"=>$bookId,
											"para"=>$para,
											"begin"=>$begin,
											"end"=>$end,
										   "text" => "", 
										   "lang" => "", 
										   "channal" => $value,
										   "status" => 0,
										   "editor" => false,
										   "editor_name"=>false,
										   "update_time"=>false
										);
                }
                $tran_channal[$value] = $value;
            }
        }
        $tran_count = 1;
    } catch (Exception $e) {
        $tran = $e->getMessage();
        //echo 'Caught exception: ',  $e->getMessage(), "\n";
    }

    foreach ($tran_channal as $key => $value) {
        # code...
        $tran_channal[$key] = $_channal->getChannal($key);
		$tran_channal[$key]["mypower"] = $_channal->getPower($key);
    }
    foreach ($translation as $key => $value) {
        # code...
        if (!empty($value["channal"])) {
            $translation[$key]["channalinfo"] = $tran_channal[$value["channal"]];
            $translation[$key]["mypower"] = $tran_channal[$value["channal"]]["mypower"];
            $translation[$key]["status"] = $tran_channal[$value["channal"]]["status"];
			#查询句子pr
			$translation[$key]["pr_new"]=$_sentPr->getNewPrNumber($value["book"],$value["para"],$value["begin"],$value["end"],$value["channal"]);
			$translation[$key]["pr_all"]=$_sentPr->getAllPrNumber($value["book"],$value["para"],$value["begin"],$value["end"],$value["channal"]);
        } else {
            $translation[$key]["channalinfo"] = false;
        }
    }

    //查询路径
	$arrPath = $_pali_text->getPath($bookId, $para);
	if(count($arrPath)>0){
		$ref = $_pali_text->getPathHtml($arrPath);
		$pathTopLevel = $arrPath[count($arrPath)-1];
		$bookTitle = $_pali_book->getBookTitle($pathTopLevel["book"],$pathTopLevel["para"]);
	}
	else{
		$ref="";
		$bookTitle="";
	}

    //$para_path = _get_para_path($bookId, $para);

    $output[] = array("id" => $id,
        "palitext" => $palitext,
        "pali_sent_id" => $pali_text_id,
        "tran" => $tran,
        "translation" => $translation,
        "ref" => $ref,
		"booktitle"=>$bookTitle,
        "tran_count" => $tran_count,
        "book" => $bookId,
        "para" => $para,
        "begin" => $begin,
        "end" => $end,
        "sim" => $pali_sim,
    );

}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
