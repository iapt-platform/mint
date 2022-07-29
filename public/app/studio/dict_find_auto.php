<?php
include("../log/pref_log.php");
require_once "../config.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once '../ucenter/setting_function.php';
require_once "../redis/function.php";

$redis = redis_connect();

global $error;
$error = array();
set_error_handler(function(int $number, string $message) {
	global $error;
	$error[] =  "Handler captured error $number: '$message'" . PHP_EOL  ;
});

$user_setting = get_setting();

if (isset($_GET["book"])) {
    $in_book = (int)$_GET["book"];
}
if (isset($_GET["para"])) {
    $in_para = (int)$_GET["para"];
}
$para_list = str_getcsv($in_para);
$strQueryPara = "("; //单词查询字串
foreach ($para_list as $para) {
    $strQueryPara .= "'{$para}',";
}
$strQueryPara = mb_substr($strQueryPara, 0, mb_strlen($strQueryPara, "UTF-8") - 1, "UTF-8");
$strQueryPara .= ")";

if (isset($_GET["debug"])) {
    $debug = true;
} else {
    $debug = false;
}

function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float) $usec + (float) $sec);
}

$time_start = microtime_float();

//open database

global $PDO;

//查询单词表
$db_file = _DIR_PALICANON_TEMPLET_ . "/p" . $in_book . "_tpl.db3";
PDO_Connect(_FILE_DB_PALICANON_TEMPLET_);
$query = "SELECT paragraph,wid,real FROM "._TABLE_PALICANON_TEMPLET_." WHERE ( book = ".$PDO->quote($in_book)." AND paragraph  in " . $strQueryPara . " ) and  real <> '' and  type <> '.ctl.' ";
if ($debug) {
    
    echo $query . "<br>";
}
$FetchAllWord = PDO_FetchAll($query);
$iFetch = count($FetchAllWord);
if ($iFetch == 0) {
    echo json_encode(array(), JSON_UNESCAPED_UNICODE);
    exit;
}
$voc_list = array();

foreach ($FetchAllWord as $word) {
    $voc_list[$word["real"]] = 1;
}
if ($debug) {
    echo "单词表共计：" . count($voc_list) . "词<br>";
}

//查询单词表结束

$word_list = array();
foreach ($voc_list as $word => $value) {
    array_push($word_list, $word);
}
$lookup_loop = 2;

$dict_word_spell = array();
$output = array();
$db_file_list = array();
//字典列表
/*
array_push($db_file_list, array(_FILE_DB_WBW1_, " ORDER BY rowid DESC"));

array_push($db_file_list, array(_DIR_DICT_SYSTEM_ . "/sys_regular.db", " ORDER BY confidence DESC"));
array_push($db_file_list, array(_DIR_DICT_SYSTEM_ . "/sys_irregular.db", ""));
array_push($db_file_list, array(_DIR_DICT_SYSTEM_ . "/union.db", ""));
array_push($db_file_list, array(_DIR_DICT_SYSTEM_ . "/comp.db", ""));

array_push($db_file_list, array(_DIR_DICT_3RD_ . "/pm.db", ""));
array_push($db_file_list, array(_DIR_DICT_3RD_ . "/bhmf.db", ""));
array_push($db_file_list, array(_DIR_DICT_3RD_ . "/shuihan.db", ""));
array_push($db_file_list, array(_DIR_DICT_3RD_ . "/concise.db", ""));
array_push($db_file_list, array(_DIR_DICT_3RD_ . "/uhan_en.db", ""));
*/

$db_file_list[] = array("","wbwdict://new/".$_COOKIE["userid"],true);	
$db_file_list[] = array(_FILE_DB_TERM_,"dict://term",true);	
$db_file_list[] = array(_FILE_DB_WBW1_,Redis["prefix"]."dict/user",true);

$db_file_list[] = array( _DIR_DICT_SYSTEM_ . "/sys_regular.db","dict://regular",true);
$db_file_list[] = array( _DIR_DICT_SYSTEM_ . "/sys_irregular.db","dict://irregular",true);
$db_file_list[] = array( _DIR_DICT_SYSTEM_ . "/union.db","dict://union",true);
$db_file_list[] = array( _DIR_DICT_SYSTEM_ . "/comp.db","dict://comp",true);

$db_file_list[] = array( _DIR_DICT_3RD_ . "/pm.db","dict://pm",true);
$db_file_list[] = array( _DIR_DICT_3RD_ . "/bhmf.db","dict://bhmf",true);
$db_file_list[] = array( _DIR_DICT_3RD_ . "/shuihan.db","dict://shuihan",true);
$db_file_list[] = array( _DIR_DICT_3RD_ . "/concise.db","dict://concise",true);
$db_file_list[] = array( _DIR_DICT_3RD_ . "/uhan_en.db","dict://uhan_en",true);

$_dict_db = array();
foreach ($db_file_list as $db_file) {
    try {
		if ($redis && !empty($db_file[1])) {
			$dbh=null;
		}
		else{
			$dbh = new PDO("sqlite:" . $db_file[0], "", "");
			$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
			$dbh->query("PRAGMA synchronous = OFF");
			$dbh->query("PRAGMA journal_mode = WAL");
			$dbh->query("PRAGMA foreign_keys = ON");
			$dbh->query("PRAGMA busy_timeout = 5000");			
		}
        $_dict_db[] = array("file" => $db_file[0], "dbh" => $dbh,"redis"=>$db_file[1],"static"=>$db_file[2]);

    } catch (PDOException $e) {
        if ($debug) {
            print "Error!: " . $e->getMessage() . "<br/>";
        }
    }
}

$lookuped=array();

for ($i = 0; $i < $lookup_loop; $i++) {
    $parent_list = array();

	# 记录已经查过的词，下次就不查了
	$newWordList = array();
	foreach ($word_list as $lsWord) {
		if(!isset($lookuped[$lsWord]) && !empty($lsWord)){
			$newWordList[]=$lsWord;
			$lookuped[$lsWord]=1;
		}
	}
	if(count($newWordList)==0){
		break;
	}
	$word_list = $newWordList;
	# 记录已经查过的词结束

    $strQueryWord = "("; //单词查询字串
    foreach ($word_list as $word) {
        $word = str_replace("'", "’", $word);
        $strQueryWord .= "'{$word}',";
    }
    $strQueryWord = mb_substr($strQueryWord, 0, mb_strlen($strQueryWord, "UTF-8") - 1, "UTF-8");
    $strQueryWord .= ")";
    if ($debug) {
        echo "<h2>第{$i}轮查询：$strQueryWord</h2>";
    }
    foreach ($_dict_db as $db) {
        $db_file = $db["file"];
        if ($debug) {
            echo "dict:$db_file<br>";
		}
        $strOrderby = $db["file"][1];

        if ($i == 0) {
            $query = "select * from dict where  pali  in {$strQueryWord} AND ( type <> '.n:base.' AND  type <> '.ti:base.' AND  type <> '.adj:base.'  AND  type <> '.pron:base.'  AND  type <> '.v:base.'  AND  type <> '.part.' ) " . $strOrderby;
        } else {
            $query = "select * from dict where   pali  in {$strQueryWord}  " . $strOrderby;
        }

        if ($debug) {
            echo $query . "<br>";
        }

		$Fetch = array();
		if ($redis && !empty($db["redis"])) {
			if ($debug) {
				echo "<spen style='color:green;'>redis</spen>:{$db["redis"]}<br>";
			}
			foreach ($word_list as $word) {
				$wordData = $redis->hGet($db["redis"],$word);
				if($wordData){
					if(!empty($wordData)){
						$arrWord = json_decode($wordData,true);
						foreach ($arrWord as  $one) {
							# code...
							if(count($one)==14){
								$Fetch[] = array("id"=>$one[0],
												"pali"=>$one[1],
												"type"=>$one[2],
												"gramma"=>$one[3],
												"parent"=>$one[4],
												"mean"=>$one[5],
												"note"=>$one[6],
												"parts"=>$one[7],
												"partmean"=>$one[8],
												"status"=>$one[9],
												"confidence"=>$one[10],
												"dict_name"=>$one[12],
												"lang"=>$one[13]
												);
							}
							else{
								$Fetch[] = array("id"=>$one[0],
												"pali"=>$one[1],
												"type"=>$one[2],
												"gramma"=>$one[3],
												"parent"=>$one[4],
												"mean"=>$one[5],
												"note"=>$one[6],
												"parts"=>$one[7],
												"partmean"=>"",
												"status"=>$one[8],
												"confidence"=>$one[9],
												"dict_name"=>$one[10],
												"lang"=>$one[12]
												);								
							}
						}						
					}
				}
				else{
					#  没找到就不找了
				}
			}
		}
		else{
			try {
				//$Fetch = PDO_FetchAll($query);
				$stmt = $db["dbh"]->query($query);
				if ($stmt) {
					$Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
				} else {
					$Fetch = array();
					if ($debug) {
						echo "无效的Statement句柄";
					}
				}
			} catch (Exception $e) {
				if ($debug) {
					echo 'Caught exception: ', $e->getMessage(), "\n";
				}
				continue;
			}
		}

        $iFetch = count($Fetch);
        if ($debug) {
            echo "count:{$iFetch}<br>";
        }
        if ($iFetch > 0) {
            foreach ($Fetch as $one) {
                $id = $one["id"];
                if (isset($one["guid"])) {
                    $guid = $one["guid"];
                } else {
                    $guid = "";
                }

                if (isset($one["lang"])) {
                    $language = $one["lang"];
                } else if (isset($one["language"])) {
                    $language = $one["language"];
                } else {
                    $language = "en";
                }
                $pali = $one["pali"];
                $dict_word_spell["{$pali}"] = 1;
                $type = $one["type"];
                $gramma = $one["gramma"];
                $parent = $one["parent"];
                if (inLangSetting($language, $user_setting["dict.lang"])) {
                    $mean = $one["mean"];
                } else {
                    $mean = "";
                }

                if (isset($one["note"])) {
                    $note = $one["note"];
                } else {
                    $note = "";
                }

                if (isset($one["parts"])) {
                    $parts = $one["parts"];
                } else if (isset($one["factors"])) {
                    $parts = $one["factors"];
                } else {
                    $parts = "";
                }

                if (isset($one["partmean"])) {
                    $partmean = $one["partmean"];
                } else if (isset($one["factormean"])) {
                    $partmean = $one["factormean"];
                } else {
                    $partmean = "";
                }
                if (inLangSetting($language, $user_setting["dict.lang"]) == false) {
                    $partmean = "";
                }
                if (isset($one["part_id"])) {
                    $part_id = $one["part_id"];
                } else {
                    $part_id = "";
                }

                if (isset($one["status"])) {
                    $status = $one["status"];
                } else {
                    $status = "";
                }

                if (isset($one["dict_name"])) {
                    $dict_name = $one["dict_name"];
                } else {
                    $dict_name = "";
                }

                array_push($output, array(
                    "id" => $id,
                    "guid" => $guid,
                    "pali" => $pali,
                    "type" => $type,
                    "gramma" => $gramma,
                    "parent" => $parent,
                    "mean" => $mean,
                    "note" => $note,
                    "parts" => $parts,
                    "part_id" => $part_id,
                    "partmean" => $partmean,
                    "status" => $status,
                    "dict_name" => $dict_name,
                    "language" => $language,
                ));
                if (!empty($parent)) {
                    if ($pali != $parent) {
                        $parent_list[$one["parent"]] = 1;
                    }
                }
                if ($type != "part") {
                    if (isset($one["factors"])) {
                        $parts = str_getcsv($one["factors"], '+');
                        foreach ($parts as $x) {
                            if (!empty($x)) {
                                if ($x != $pali) {
                                    $parent_list[$x] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }
        $PDO = null;
    }
    /*
    if($i==0){
    //自动查找单词词干
    $word_base=getPaliWordBase($in_word);
    foreach($word_base as $x=>$infolist){
    foreach($infolist as $gramma){
    array_push($output,
    array("pali"=>$in_word,
    "type"=>$gramma["type"],
    "gramma"=>$gramma["gramma"],
    "mean"=>"",
    "parent"=>$x,
    "parts"=>$gramma["parts"],
    "partmean"=>"",
    "language"=>"en",
    "dict_name"=>"auto",
    "status"=>128
    ));
    $part_list=str_getcsv($gramma["parts"],"+");
    foreach($part_list as $part){
    $parent_list[$part]=1;
    }
    }
    }
    }
     */

    if ($debug) {
        echo "parent:" . count($parent_list) . "<br>";
        //print_r($parent_list)."<br>";
    }
    if (count($parent_list) == 0) {
        break;
    } else {
        $word_list = array();
        foreach ($parent_list as $x => $value) {
            array_push($word_list, $x);
        }
    }

}
//查询结束
//删除无效数据
$newOutput = array();
foreach ($output as $value) {

    if ($value["dict_name"] == "auto") {
        if (isset($dict_word_spell["{$value["parent"]}"])) {
            array_push($newOutput, $value);
        }
    } else {
        array_push($newOutput, $value);
    }
}

if ($debug) {
    echo "<textarea width=\"100%\" >";

    echo json_encode($newOutput, JSON_UNESCAPED_UNICODE);

    echo "</textarea>";
}

if ($debug) {
    echo "生成：" . count($output) . "<br>";
    echo "有效：" . count($newOutput) . "<br>";

}
//开始匹配
$counter = 0;
$output = array();
foreach ($FetchAllWord as $word) {
    $pali = $word["real"];
    $type = "";
    $gramma = "";
    $mean = "";
    $parent = "";
    $parts = "";
    $partmean = "";
    foreach ($newOutput as $dictword) {
        if ($dictword["pali"] == $pali) {
            if ($type == "" && $gramma == "") {
                $type = $dictword["type"];
                $gramma = $dictword["gramma"];
            }
            if (trim($mean) == "") {
                $mean = str_getcsv($dictword["mean"], "$")[0];

            }
            if ($parent == "") {
                $parent = $dictword["parent"];
            }
            if ($parts == "") {
                $parts = $dictword["parts"];
            }
            if ($partmean == "") {
                $partmean = $dictword["partmean"];
            }
        }

    }
    if ($mean == "" && $parent != "") {
        foreach ($newOutput as $parentword) {
            if ($parentword["pali"] == $parent) {
                if ($parentword["mean"] != "") {
                    $mean = trim(str_getcsv($parentword["mean"], "$")[0]);
                    if ($mean != "") {
                        break;
                    }
                }
            }
        }
    }
    if ($type != "" ||
        $gramma != "" ||
        $mean != "" ||
        $parent != "" ||
        $parts != "" ||
        $partmean != "") {
        $counter++;
    }
    array_push($output,
        array("book" => $in_book,
            "paragraph" => $word["paragraph"],
            "num" => $word["wid"],
            "pali" => $word["real"],
            "type" => $type,
            "gramma" => $gramma,
            "mean" => $mean,
            "parent" => $parent,
            "parts" => $parts,
            "partmean" => $partmean,
            "status" => 3,
        ));
}
if ($debug) {
    echo "<textarea width=\"100%\" >";
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
if ($debug) {
    echo "</textarea>";
}
if ($debug) {
    echo "匹配" . (($counter / count($FetchAllWord)) * 100) . "<br>";
    foreach ($output as $result) {
        //echo "{$result["pali"]}-{$result["mean"]}-{$result["parent"]}<br>";
    }
    $queryTime = (microtime_float() - $time_start) * 1000;
    echo "<div >搜索时间：$queryTime 毫秒</div>";
}
PrefLog();