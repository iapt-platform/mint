<?php
//查询参考字典
include("../log/pref_log.php");
require_once '../path.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/load_lang.php"; //语言文件
require_once "../public/function.php";
require_once "../search/word_function.php";
require_once "../ucenter/active.php";
require_once "../ucenter/function.php";
require_once "../dict/p_ending.php";
require_once "../redis/function.php";

global $redis;
$redis = redis_connect();

global $count_return;
$count_return = 0;

$word = mb_strtolower($_GET["word"], 'UTF-8');
$org_word = $word;


global $dict_list;

$dict_list = array();

$right_word_list = "";

        add_edit_event(_DICT_LOOKUP_, $word);

		echo "<div id='dict_ref'>";
		#先查原词
		echo "<div class='pali_spell'><a name='{word_$word}'></a>" . $word . "</div>";
		$dict_list_a = [];
		
		//社区字典开始
		echo lookup_user($word);
		//社区字典结束
		echo lookup_term($word);


        PDO_Connect("" . _FILE_DB_REF_);
        //直接查询
        $query = "SELECT dict.dict_id,dict.mean,info.shortname from " . _TABLE_DICT_REF_ . " LEFT JOIN info ON dict.dict_id = info.id where word = ? limit 0,100";

        $Fetch = PDO_FetchAll($query, array($word));
        $iFetch = count($Fetch);
		echo "直接查询{$iFetch}<br>";
        $count_return += $iFetch;
        if ($iFetch > 0) {
            for ($i = 0; $i < $iFetch; $i++) {
                $mean = $Fetch[$i]["mean"];
                $dictid = $Fetch[$i]["dict_id"];
                $dict_list[$dictid] = $Fetch[$i]["shortname"];
                $dict_list_a[] = array("ref_dict_$dictid", $Fetch[$i]["shortname"]);
                $outXml = "<div class='dict_word'>";
                $outXml = $outXml . "<a name='ref_dict_$dictid'></a>";
                $outXml = $outXml . "<div class='dict'>" . $Fetch[$i]["shortname"] . "</div>";
                $outXml = $outXml . "<div class='mean'>" . $mean . "</div>";
                $outXml = $outXml . "</div>";
                echo $outXml;
            }
		}
		

        //去格位除尾查
        $newWord = array();
        for ($row = 0; $row < count($case); $row++) {
            $len = mb_strlen($case[$row][1], "UTF-8");
            $end = mb_substr($word, 0 - $len, null, "UTF-8");
            if ($end == $case[$row][1]) {
                $base = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $len, "UTF-8") . $case[$row][0];
                if ($base != $word) {
                    $thiscase = "";
                    $arrCase = explode('$', $case[$row][2]);
                    foreach ($arrCase as $value) {
                        $caseid = "grammar_" . str_replace('.', '', $value);
                        $thiscase .= "<guide gid='$caseid'>$value</guide>";
                    }

                    if (isset($newWord[$base])) {
                        $newWord[$base] .= "<br />" . $thiscase;
                    } else {
                        $newWord[$base] = $thiscase;
                    }
                }
            }
        }

		$base_list = array();
        if (count($newWord) > 0) {
            foreach ($newWord as $x => $x_value) {
				$titleHas = false;
				$userDictStr = lookup_user($x);
				$termDictStr = lookup_term($x);

				if(!empty($userDictStr) || !empty($termDictStr)){
					echo "<div class='pali_spell'><a name='word_$x'></a>" . $x . "</div>";
					$titleHas = true;
				}

				
                $query = "SELECT dict.dict_id,dict.mean,info.shortname from " . _TABLE_DICT_REF_ . " LEFT JOIN info ON dict.dict_id = info.id where word = ? limit 0,30";
                $Fetch = PDO_FetchAll($query, array($x));
                $iFetch = count($Fetch);
                $count_return += $iFetch;
                if ($iFetch > 0) {
					$base_list[] = $x;
                    $dict_list_a[] = array("word_$x", $x);
					if(!$titleHas){
						echo "<div class='pali_spell'><a name='word_$x'></a>" . $x . "</div>";
					}
					
                    //语法信息
                    foreach ($_local->grammastr as $gr) {
                        $x_value = str_replace($gr->id, $gr->value, $x_value);
                    }
                    echo "<div class='dict_find_gramma'>" . $x_value . "</div>";
					
					echo $userDictStr;
					echo $termDictStr;

                    for ($i = 0; $i < $iFetch; $i++) {
                        $mean = $Fetch[$i]["mean"];
                        $dictid = $Fetch[$i]["dict_id"];
                        $dict_list[$dictid] = $Fetch[$i]["shortname"];
                        $dict_list_a[] = array("ref_dict_$dictid", $Fetch[$i]["shortname"]);
                        echo "<div class='dict_word'>";
                        echo "<a name='ref_dict_$dictid'></a>";
                        echo "<div class='dict'>" . $Fetch[$i]["shortname"] . "</div>";
                        echo "<div class='mean'>" . $mean . "</div>";
                        echo "</div>";
                    }
                }
            }
        }
		//去除尾查结束

		//去分词除尾查
        $arrBase = array();		
		if (count($newWord) > 0) {
			foreach ($newWord as $base => $grammar){
				for ($row = 0; $row < count($p_ending); $row++) {
					$len = mb_strlen($p_ending[$row][1], "UTF-8");
					$end = mb_substr($base, 0 - $len, null, "UTF-8");
					if ($end == $p_ending[$row][1]) {
						$newbase = mb_substr($base, 0, mb_strlen($base, "UTF-8") - $len, "UTF-8") . $p_ending[$row][0];
						if ($newbase != $base) {
							$thiscase = "";
							$arrCase = explode('$', $p_ending[$row][2]);
							foreach ($arrCase as $value) {
								$caseid = "grammar_" . str_replace('.', '', $value);
								$thiscase .= "<guide gid='$caseid'>$value</guide>";
							}

							if (isset($arrBase[$newbase])) {
								$arrBase[$newbase]['grammar'] .= "<br />" . $thiscase;
							} else {
								$arrBase[$newbase]['grammar'] = $thiscase;
								$arrBase[$newbase]['parent'] = $base;

							}
						}
					}
				}
			}

			$base_list = array();
			if (count($arrBase) > 0) {
				foreach ($arrBase as $x => $x_value) {
					
					$query = "SELECT dict.dict_id,dict.mean,info.shortname from " . _TABLE_DICT_REF_ . " LEFT JOIN info ON dict.dict_id = info.id where word = ? limit 0,30";
					$Fetch = PDO_FetchAll($query, array($x));
					$iFetch = count($Fetch);	
					$count_return += $iFetch;
					if ($iFetch > 0) {
						$base_list[] = $x;
						$dict_list_a[] = array("word_$x", $x);
						echo "<div class='pali_spell'><a name='word_$x'></a>" . $x . "</div>";
						echo "<div style='color:gray;'>{$x}➡{$x_value["parent"]}➡{$word}</div>";
						//替换为本地语法信息
						foreach ($_local->grammastr as $gr) {
							$x_value['grammar'] = str_replace($gr->id, $gr->value, $x_value['grammar']);
						}
						echo "<div class='dict_find_gramma'>" . $x_value['grammar'] . "</div>";
						for ($i = 0; $i < $iFetch; $i++) {
							$mean = $Fetch[$i]["mean"];
							$dictid = $Fetch[$i]["dict_id"];
							$dict_list[$dictid] = $Fetch[$i]["shortname"];
							$dict_list_a[] = array("ref_dict_$dictid", $Fetch[$i]["shortname"]);
							echo "<div class='dict_word'>";
							echo "<a name='ref_dict_$dictid'></a>";
							echo "<div class='dict'>" . $Fetch[$i]["shortname"] . "</div>";
							echo "<div class='mean'>" . $mean . "</div>";
							echo "</div>";
						}
					}
				}
			}
		}
		//去除尾查结束


		echo "<div id='search_summary'>";
		echo "查询{$word} 共{$count_return}条 ";
		if(count($base_list)>0){
			echo "找到可能的拼写： ";
			foreach ($base_list as $key => $value) {
				# code...
				echo "<a>{$value}</a> ";
			}
		}
		echo "<a>查询内文</a>";
		echo "</div>";
		echo "<input type='hidden' id='word_count' value='{$count_return}' />";

		//查连读词
		/*
        if ($count_return < 2) {
            echo "<div>Junction</div>";
            $newWord = array();
            for ($row = 0; $row < count($un); $row++) {
                $len = mb_strlen($un[$row][1], "UTF-8");
                $end = mb_substr($word, 0 - $len, null, "UTF-8");
                if ($end == $un[$row][1]) {
                    $base = mb_substr($word, 0, mb_strlen($word, "UTF-8") - $len, "UTF-8") . $un[$row][0];
                    $arr_un = explode("+", $base);
                    foreach ($arr_un as $oneword) {
                        echo "<a onclick='dict_pre_word_click(\"$oneword\")'>$oneword</a> + ";
                    }
                    echo "<br />";
                }
            }
		}
		*/
		//拆复合词
		echo "<div id='auto_split'></div>";


        //查内容
        if ($count_return < 4) {
            $word1 = $org_word;
            $wordInMean = "%$org_word%";
            echo "包含 $org_word 的:<br />";
            $query = "SELECT dict.dict_id,dict.word,dict.mean,info.shortname from " . _TABLE_DICT_REF_ . " LEFT JOIN info ON dict.dict_id = info.id where mean like ? limit 0,30";
            $Fetch = PDO_FetchAll($query, array($wordInMean));
            $iFetch = count($Fetch);
            $count_return += $iFetch;
            if ($iFetch > 0) {
                for ($i = 0; $i < $iFetch; $i++) {
                    $mean = $Fetch[$i]["mean"];
                    $pos = mb_stripos($mean, $word, 0, "UTF-8");
                    if ($pos) {
                        if ($pos > 20) {
                            $start = $pos - 20;
                        } else {
                            $start = 0;
                        }
                        $newmean = mb_substr($mean, $start, 100, "UTF-8");
                    } else {
                        $newmean = $mean;
                    }
                    $pos = mb_stripos($newmean, $word1, 0, "UTF-8");
                    $head = mb_substr($newmean, 0, $pos, "UTF-8");
                    $mid = mb_substr($newmean, $pos, mb_strlen($word1, "UTF-8"), "UTF-8");
                    $end = mb_substr($newmean, $pos + mb_strlen($word1, "UTF-8"), null, "UTF-8");
                    $heigh_light_mean = "$head<hl>$mid</hl>$end";
                    echo "<div class='dict_word'>";
                    echo "<div class='pali'><a href='index.php?key={$Fetch[$i]["word"]}'>" . $Fetch[$i]["word"] . "</a></div>";
                    echo "<div class='dict'>" . $Fetch[$i]["shortname"] . "</div>";
					echo "<div class='mean'>" . $heigh_light_mean . "</div>";
					echo "<div><a href='index.php?key={$Fetch[$i]["word"]}&hightlight={$org_word}'>详情</a></div>";
                    echo "</div>";
                }
            }
		}
		else{

		}

        echo "<div id='dictlist'>";
        foreach ($dict_list_a as $x_value) {
            if (substr($x_value[0], 0, 4) == "word") {
                echo "<div class='pali_spell'>";
                echo "<a href='#{$x_value[0]}'>$x_value[1]</a></div>";
            } else {
                echo "<div><a href='#{$x_value[0]}'>$x_value[1]</a></div>";
            }
        }
        echo "<div>";

        $arrWords = countWordInPali($word, true);
        $weight = 0;
        foreach ($arrWords as $oneword) {
            $weight += $oneword["count"] * $oneword["len"];
        }
        //echo "<div>{$_local->gui->word_weight}：$weight {$_local->gui->characters}</div>";
        //echo "<div>{$_local->gui->real_declension}：".count($arrWords)." {$_local->gui->forms}</div>";
        $right_word_list .= "<div>{$_local->gui->word_weight}：$weight {$_local->gui->characters}</div>";
        $right_word_list .= "<div>{$_local->gui->real_declension}：" . count($arrWords) . " {$_local->gui->forms}</div>";
        foreach ($arrWords as $oneword) {
            if ($oneword["bold"] > 0) {
                //echo "<div><b>{$oneword["word"]}</b> {$oneword["count"]} {$_local->gui->times}</div>";
                $right_word_list .= "<div><b>{$oneword["word"]}</b> {$oneword["count"]} {$_local->gui->times}</div>";
            } else {
                //echo "<div>{$oneword["word"]} {$oneword["count"]}{$_local->gui->times}</div>";
                $right_word_list .= "<div>{$oneword["word"]} {$oneword["count"]}{$_local->gui->times}</div>";
            }
        }
        echo "</div>";
        echo "</div>";
        echo "</div>";
        //参考字典查询结束

        //用户词典编辑窗口
        echo "<div id='dict_user' >";
        echo "<div><a href='word_statistics.php?word={$word}'>";
        echo "<svg t='1596783175334' class='icon' style='font-size: xxx-large; fill: var(--link-hover-color); margin: 5px;' viewBox='0 0 1024 1024' version='1.1' xmlns='http://www.w3.org/2000/svg' p-id='7755' width='200' height='200'><path d='M1019.904 450.56L536.576 557.056l417.792 208.896C999.424 692.224 1024 606.208 1024 512c0-20.48 0-40.96-4.096-61.44z m-12.288-61.44C958.464 184.32 786.432 28.672 573.44 4.096L446.464 512l561.152-122.88zM737.28 970.752c73.728-36.864 139.264-90.112 188.416-159.744L507.904 602.112l229.376 368.64zM512 0C229.376 0 0 229.376 0 512s229.376 512 512 512c61.44 0 118.784-12.288 172.032-28.672L385.024 512 512 0z' p-id='7756'></path></svg>";
        echo "<span>{$_local->gui->click_to_chart}</span></a></div>";
		echo $right_word_list;
		
		/*
        echo "<div class='dict_word' ><b>{$_local->gui->undone_function}</b>";
        echo "<div class='' onclick=\"dict_show_edit()\">{$_local->gui->edit}</div>";
        echo "<div class='pali'>{$word}</div>";

        if ($iFetch > 0) {
            echo "<div id='user_word_edit' style='display:none'>";
        } else {
            echo "<div id='user_word_edit'>";
        }
        echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->wordtype}</legend>";
        echo "<select id=\"id_type\" name=\"type\" >";
        foreach ($_local->type_str as $type) {
            echo "<option value=\"{$type->id}\" >{$type->value}</option>";
        }
        echo "</select>";
        echo "</fieldset>";
        echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->gramma}</legend><input type='input' value=''/></fieldset>";
        echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->parent}</legend><input type='input' value=''/></fieldset>";
        echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->g_mean}</legend><input type='input' value=''/></fieldset>";
        echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->note}</legend><textarea></textarea></fieldset>";
        echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->factor}</legend><input type='input' value=''/></fieldset>";
        echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->f_mean}</legend><input type='input' value=''/></fieldset>";
        echo "<div class=''><button>{$_local->gui->add_to} {$_local->gui->my_dictionary}</button></div>";
		echo "</div>";
		

		echo "</div>";
		*/
		echo "</div>";

        //查用户词典结束

PrefLog();

function lookup_user($word){
	global $dict_list;
	global $redis;
	global $_local;
	global $PDO;
	global $count_return;

	$output ="";
	$Fetch=array();
	if($redis){
		$wordData = $redis->hGet("dict://user",$word);
			if($wordData){
				if(!empty($wordData)){
					$arrWord = json_decode($wordData,true);
					foreach ($arrWord as  $one) {
						# code...
						$Fetch[] = array("id"=>$one[0],
										"pali"=>$one[1],
										"type"=>$one[2],
										"gramma"=>$one[3],
										"parent"=>$one[4],
										"mean"=>$one[5],
										"note"=>$one[6],
										"factors"=>$one[7],
										"factormean"=>$one[8],
										"status"=>$one[9],
										"confidence"=>$one[10],
										"creator"=>$one[11],
										"dict_name"=>$one[12],
										"lang"=>$one[13],
										);
					}						
				}
			}
			else{
				#  没找到就不找了
			}
	}
	else{
		PDO_Connect("" . _FILE_DB_WBW_);
		$query = "SELECT *  from " . _TABLE_DICT_REF_ . " where pali = ? limit 0,100";
		$Fetch = PDO_FetchAll($query, array($word));
	}
	
	$iFetch = count($Fetch);
	
	if ($iFetch > 0) {
		$count_return++;
		$userlist = array();
		foreach ($Fetch as $value) {
			if (isset($userlist[$value["creator"]])) {
				$userlist[$value["creator"]] += 1;
			} else {
				$userlist[$value["creator"]] = 1;
			}
			$userwordcase = $value["type"] . "#" . $value["gramma"];
			$parent = $value["parent"];
			if(empty($parent)){
				$parent = "_null_";
			}
			if (isset($userdict["{$parent}"])) {
				$userdict["{$parent}"]["mean"] .= "$". $value["mean"] ;
				$userdict["{$parent}"]["factors"] .= "@". $value["factors"];
				$userdict["{$parent}"]["case"] .= "@".$userwordcase;

			} else {
				$userdict["{$parent}"]["mean"] = $value["mean"];
				$userdict["{$parent}"]["factors"] = $value["factors"];
				$userdict["{$parent}"]["case"] = $userwordcase;
			}

		}
		$output .= "<div class='dict_word'>";
		$output .= "<div class='dict'>{$_local->gui->com_dict}</div><a name='net'></a>";
		$dict_list_a[] = array("net", $_local->gui->com_dict);


		foreach ($userdict as $key => $value) {
			#语法信息查重
			$thiscase = array();
			$strCase = "";
			$arrCase = explode("@",$value["case"]);
			foreach ($arrCase as  $case) {
				# code...
				$thiscase[$case] = 1;
			}
			foreach ($thiscase as $case => $casevalue) {
				# code...
				$strCase .=$case . "; ";
			}
			#语法信息替换为本地字符串
			foreach ($_local->grammastr as $gr) {
				$strCase = str_replace($gr->id, $gr->value, $strCase);
			}
			#拆分查重复
			$thispart = array();
			$strPart = "";
			$arrPart = explode("@",$value["factors"]);
			foreach ($arrPart as  $part) {
				# code...
				$thispart[$part] = 1;
			}
			foreach ($thispart as $part => $partvalue) {
				# code...
				$strPart .=$part . "; ";
			}

			#意思查重复
			$thismean = array();
			$strMean = "";
			$arrMean = explode("$",$value["mean"]);
			foreach ($arrMean as  $mean) {
				# code...
				$thismean[$mean] = 1;
			}
			foreach ($thismean as $mean => $meanvalue) {
				# code...
				$strMean .=$mean . "; ";
			}
			$output .= "<div class='mean'><b>语法</b>:{$strCase}</div>";
			if($key!=="_null_"){
				$output .= "<div class='mean'><b>原型</b>:<a href='index.php?key={$key}'>{$key}</a></div>";				
			}

			$output .= "<div class='mean'><b>意思</b>:{$strMean}</div>";
			$output .= "<div class='mean'><b>组成</b>:{$strPart}</div>";
		}
		$output .= "<div><span>{$_local->gui->contributor}：</span>";
		$userinfo = new UserInfo();
		foreach ($userlist as $key => $value) {
			$user = $userinfo->getName($key);
			$output .= $user["nickname"] . " ";
		}
		$output .= "</div>";
		$output .= "</div>";
	}
	
	return $output;

}

function lookup_term($word){
	global $dict_list;
	global $redis;
	global $_local;
	global $PDO;
	global $count_return;

	$output ="";
	$Fetch=array();
	if($redis){
		$wordData = $redis->hGet("dict://term",$word);
			if($wordData){
				if(!empty($wordData)){
					$arrWord = json_decode($wordData,true);
					foreach ($arrWord as  $one) {
						# code...
						$Fetch[] = array("id"=>$one[0],
										"pali"=>$one[1],
										"type"=>$one[2],
										"gramma"=>$one[3],
										"parent"=>$one[4],
										"mean"=>$one[5],
										"note"=>$one[6],
										"factors"=>$one[7],
										"factormean"=>$one[8],
										"status"=>$one[9],
										"confidence"=>$one[10],
										"creator"=>$one[11],
										"dict_name"=>$one[12],
										"lang"=>$one[13],
										);
					}						
				}
			}
			else{
				#  没找到就不找了
			}
	}
	else{
		PDO_Connect("" . _FILE_DB_WBW_);
		$query = "SELECT *  from " . _TABLE_DICT_REF_ . " where pali = ? limit 0,100";
		$Fetch = PDO_FetchAll($query, array($word));
	}
	
	$iFetch = count($Fetch);
	$count_return += $iFetch;
	if ($iFetch > 0) {
		$userlist = array();
		foreach ($Fetch as $value) {
			if (isset($userlist[$value["creator"]])) {
				$userlist[$value["creator"]] += 1;
			} else {
				$userlist[$value["creator"]] = 1;
			}
			$userwordcase = $value["type"] . "#" . $value["gramma"];
			if (isset($userdict["{$userwordcase}"])) {
				$userdict["{$userwordcase}"]["mean"] .= $value["mean"] . ";";
				$userdict["{$userwordcase}"]["factors"] .= $value["factors"];
			} else {
				$userdict["{$userwordcase}"]["mean"] = $value["mean"];
				$userdict["{$userwordcase}"]["factors"] = $value["factors"];
			}

		}
		$output .= "<div class='dict_word'>";
		$output .= "<div class='dict'>{$_local->gui->wiki_term}</div><a name='net'></a>";
		$dict_list_a[] = array("net", $_local->gui->wiki_term);

		foreach ($userdict as $key => $value) {
			$output .= "<div class='mean'>{$key}:{$value["mean"]}</div>";
		}
		$output .= "<div><span>{$_local->gui->contributor}：</span>";
		$userinfo = new UserInfo();
		foreach ($userlist as $key => $value) {
			$user = $userinfo->getName($key);
			$output .= $user["nickname"] . " ";
		}
		$output .= "</div>";
		$output .= "</div>";
	}
	
	return $output;

}