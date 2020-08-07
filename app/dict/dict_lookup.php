<?php
//查询参考字典
require_once '../path.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/load_lang.php";//语言文件
require_once "../public/function.php";
require_once "../search/word_function.php";

_load_book_index();


$op=$_GET["op"];
$word=mb_strtolower($_GET["word"],'UTF-8');
$org_word=$word;

$count_return=0;
$dict_list=array();


global $PDO;
function isExsit($word){
global $PDO;
		$query = "select count(*) as co from dict where \"word\" = ".$PDO->quote($word);
		$row=PDO_FetchOne($query);
		/*
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		$count_return+=$iFetch;
		if($iFetch>0){
		
		}
		*/
		if($row[0]==0){
			return false;
		}
		else{
			return true;
		}
}

function myfunction($v1,$v2)
{
return $v1 . "+" . $v2;
}

function mySplit($strWord){
	//echo("<br>".$strWord."<br>");
	$doubleword="kkggccjjṭṭḍḍttddppbb";
	$len=mb_strlen($strWord,"UTF-8");
	if($len>5){
		for($i=$len-1;$i>3;$i--){
			$str1=mb_substr($strWord,0,$i,"UTF-8");
			$str2=mb_substr($strWord,$i,NULL,"UTF-8");
			//echo "$str1 + $str2 = ";
			if(isExsit($str1)){
				//echo "match";
				$left2=mb_substr($str2,0,2,"UTF-8");
				if(mb_strpos($doubleword,$left2,0,"UTF-8")!==FALSE){
					$str2=mb_substr($str2,1,NULL,"UTF-8");
				}
				return array($str1,$str2);
			}
			else{
				$str1=$str1."a";
				if(isExsit($str1)){
					//echo "match";
					$left2=mb_substr($str2,0,2,"UTF-8");
					if(mb_strpos($doubleword,$left2,0,"UTF-8")!==FALSE){
						$str2=mb_substr($str2,1,NULL,"UTF-8");
					}
					return array($str1,$str2);
				}
			}
		}
		//not found
		if(mb_substr($strWord,0,1,"UTF-8")=="ā"){
			$strWord='a'.mb_substr($strWord,1,NULL,"UTF-8");
			for($i=$len-1;$i>3;$i--){
				$str1=mb_substr($strWord,0,$i,"UTF-8");
				$str2=mb_substr($strWord,$i,NULL,"UTF-8");
				//echo "$str1 + $str2 = ";
				if(isExsit($str1)){
					//echo "match";
					$left2=mb_substr($str2,0,2,"UTF-8");
					if(mb_strpos($doubleword,$left2,0,"UTF-8")!==FALSE){
						$str2=mb_substr($str2,1,NULL,"UTF-8");
					}
					return array($str1,$str2);
				}
				else{
					$str1=$str1."a";
					if(isExsit($str1)){
						//echo "match";
						$left2=mb_substr($str2,0,2,"UTF-8");
						if(mb_strpos($doubleword,$left2,0,"UTF-8")!==FALSE){
							$str2=mb_substr($str2,1,NULL,"UTF-8");
						}
						return array($str1,$str2);
					}
				}
			}			
		}
		//not found
		if(mb_substr($strWord,0,1,"UTF-8")=="e"){
			$strWord='i'.mb_substr($strWord,1,NULL,"UTF-8");
			for($i=$len-1;$i>3;$i--){
				$str1=mb_substr($strWord,0,$i,"UTF-8");
				$str2=mb_substr($strWord,$i,NULL,"UTF-8");
				//echo "$str1 + $str2 = ";
				if(isExsit($str1)){
					//echo "match";
					$left2=mb_substr($str2,0,2,"UTF-8");
					if(mb_strpos($doubleword,$left2,0,"UTF-8")!==FALSE){
						$str2=mb_substr($str2,1,NULL,"UTF-8");
					}
					return array($str1,$str2);
				}
				else{
					$str1=$str1."a";
					if(isExsit($str1)){
						//echo "match";
						$left2=mb_substr($str2,0,2,"UTF-8");
						if(mb_strpos($doubleword,$left2,0,"UTF-8")!==FALSE){
							$str2=mb_substr($str2,1,NULL,"UTF-8");
						}
						return array($str1,$str2);
					}
				}
			}			
		}		
	}
	return(FALSE);
}


switch($op){
	case "pre"://预查询
		PDO_Connect("sqlite:"._FILE_DB_REF_INDEX_);
		echo "<div>";
		$query = "select word,count from dict where \"eword\" like ".$PDO->quote($word.'%')." OR \"word\" like ".$PDO->quote($word.'%')."  limit 0,20";

		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				$word=$Fetch[$i]["word"];
				$count=$Fetch[$i]["count"];
				echo  "<div class='dict_word_list'>";
				echo  "<a onclick='dict_pre_word_click(\"$word\")'>$word-$count</a>";
				echo  "</div>";
			}
		}
		echo "</div>";
		break;
	case "search":
		echo "<div id='dict_list'></div>";
		echo "<div id='dict_ref'>";	

		//社区字典开始
		PDO_Connect("sqlite:"._FILE_DB_WBW_);
		$query = "select *  from dict where \"pali\"= ".$PDO->quote($word)." limit 0,100";
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		$count_return+=$iFetch;
		if($iFetch>0){
			$userlist = array();
			foreach($Fetch as $value){
				if(isset($userlist[$value["creator"]])){
					$userlist[$value["creator"]] += 1;
				}
				else{
					$userlist[$value["creator"]] = 1;
				}
				$userwordcase = $value["type"]."#".$value["gramma"];
				if(isset($userdict["{$userwordcase}"])){
					$userdict["{$userwordcase}"]["mean"] .= $value["mean"].";";
					$userdict["{$userwordcase}"]["factors"] .= $value["factors"];
				}
				else{
					$userdict["{$userwordcase}"]["mean"] = $value["mean"];
					$userdict["{$userwordcase}"]["factors"] = $value["factors"];
				}
				
/*
				$mean=$Fetch[$i]["mean"];
				echo "<div class='dict_word'>";
				echo "<div class='pali'>{$word}</div>";
				echo "<div class=''>语法：{$Fetch[$i]["type"]}-{$Fetch[$i]["gramma"]}</div>";
				if(strlen($Fetch[$i]["parent"])>0){
					echo "<div class=''>语基：{$Fetch[$i]["parent"]}</div>";
				}
				echo "<div class=''>意思：{$Fetch[$i]["mean"]}</div>";
				if(strlen($Fetch[$i]["note"]>0)){
					echo "<div class=''>注解：{$Fetch[$i]["note"]}</div>";
				}
				echo "<div class=''>组分：{$Fetch[$i]["factors"]}</div>";
				echo "<div class=''>组分意思：{$Fetch[$i]["factormean"]}</div>";
				echo "<div class=''>贡献者：{$Fetch[$i]["creator"]}</div>";
				echo "<div class=''>收藏：{$Fetch[$i]["ref_counter"]}次</div>";
				echo "</div>";
				*/
			}
			echo "<div class='dict_word'>";
			echo "<div class='dict'>社区字典</div>";
			foreach($userdict as $key => $value){
				echo "<div class='mean'>{$key}:{$value["mean"]}</div>";
				
			}
			echo "<div><span>贡献者：</span>";
			foreach ($userlist as $key => $value) {
				echo $key."[".$value."]";
			}
			echo "</div>";
			echo "</div>";
		}
		//社区字典结束

		PDO_Connect("sqlite:"._FILE_DB_REF_);
		//直接查询
		$query = "select dict.dict_id,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"word\" = ".$PDO->quote($word)." limit 0,100";
		
		$Fetch = PDO_FetchAll($query);
		$iFetch=count($Fetch);
		$count_return+=$iFetch;
		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				$mean=$Fetch[$i]["mean"];
				$dictid=$Fetch[$i]["dict_id"];
				$dict_list[$dictid]=$Fetch[$i]["shortname"];
				$outXml = "<div class='dict_word'>";
				$outXml = $outXml."<a name='ref_dict_$dictid'></a>";
				$outXml = $outXml."<div class='dict'>".$Fetch[$i]["shortname"]."</div>";
				$outXml = $outXml."<div class='mean'>".$mean."</div>";
				$outXml = $outXml."</div>";
				echo $outXml;
			}
		}
		//去除尾查
		$newWord=array();
		for ($row = 0; $row < count($case); $row++) {
			$len=mb_strlen($case[$row][1],"UTF-8");
			$end=mb_substr($word, 0-$len,NULL,"UTF-8");
			if($end==$case[$row][1]){
				$base=mb_substr($word, 0,mb_strlen($word,"UTF-8")-$len,"UTF-8").$case[$row][0];
				if($base!=$word){
					if(isset($newWord[$base])){
						$newWord[$base] .= "<br />".$case[$row][2];
					}
					else{
						$newWord[$base] = $case[$row][2];
					}
				}
			}
		}

		if(count($newWord)>0){
			foreach($newWord as $x=>$x_value) {
				$query = "select dict.dict_id,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"word\" = ".$PDO->quote($x)." limit 0,30";
				$Fetch = PDO_FetchAll($query);
				$iFetch=count($Fetch);
				$count_return+=$iFetch;
				if($iFetch>0){
					echo $x . ":<div class='dict_find_gramma'>" . $x_value . "</div>";
					for($i=0;$i<$iFetch;$i++){
						$mean=$Fetch[$i]["mean"];
						$dictid=$Fetch[$i]["dict_id"];
						$dict_list[$dictid]=$Fetch[$i]["shortname"];
						echo "<div class='dict_word'>";
						echo "<a name='ref_dict_$dictid'></a>";
						echo "<div class='dict'>".$Fetch[$i]["shortname"]."</div>";
						echo "<div class='mean'>".$mean."</div>";
						echo "</div>";
					}
				}			  
			}
		}
		//去除尾查结束
		
		//查连读词
		if($count_return<2){
			echo "Junction:<br />";
			$newWord=array();
			for ($row = 0; $row < count($un); $row++) {
				$len=mb_strlen($un[$row][1],"UTF-8");
				$end=mb_substr($word, 0-$len,NULL,"UTF-8");
				if($end==$un[$row][1]){
					$base=mb_substr($word, 0,mb_strlen($word,"UTF-8")-$len,"UTF-8").$un[$row][0];
						$arr_un=explode("+",$base);
						foreach ($arr_un as $oneword)
						{
						  echo "<a onclick='dict_pre_word_click(\"$oneword\")'>$oneword</a> + ";
						}
						echo "<br />";
				}
			}		
		}
		
		//查内容
		if($count_return<2){
			$word1=$org_word;
			$wordInMean="%$org_word%";
			echo "include $org_word:<br />";
			$query = "select dict.dict_id,dict.word,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"mean\" like ".$PDO->quote($wordInMean)." limit 0,30";
			$Fetch = PDO_FetchAll($query);
			$iFetch=count($Fetch);
			$count_return+=$iFetch;
			if($iFetch>0){
				for($i=0;$i<$iFetch;$i++){
					$mean=$Fetch[$i]["mean"];
					$pos=mb_stripos($mean,$word,0,"UTF-8");
					if($pos){
						if($pos>20){
							$start=$pos-20;
						}
						else{
							$start=0;
						}
						$newmean=mb_substr($mean,$start,100,"UTF-8");
					}
					else{
						$newmean=$mean;
					}
					$pos=mb_stripos($newmean,$word1,0,"UTF-8");
					$head=mb_substr($newmean,0,$pos,"UTF-8");
					$mid=mb_substr($newmean,$pos,mb_strlen($word1,"UTF-8"),"UTF-8");
					$end=mb_substr($newmean,$pos+mb_strlen($word1,"UTF-8"),NULL,"UTF-8");
					$heigh_light_mean="$head<hl>$mid</hl>$end";
					echo "<div class='dict_word'>";
					echo "<div class='pali'>".$Fetch[$i]["word"]."</div>";
					echo "<div class='dict'>".$Fetch[$i]["shortname"]."</div>";
					echo "<div class='mean'>".$heigh_light_mean."</div>";
					echo "</div>";
				}
			}		
		}
		
		//拆复合词
		
		$splitWord=$word;
		$part=array();
		if($count_return<2){
			echo "Try to split comp:<br>";
			while(($split=mySplit($splitWord))!==FALSE){
				array_push($part,$split[0]);
				$splitWord=$split[1];
			}
			if(count($part)>0){
				array_push($part,$splitWord);
				$newPart=ltrim(array_reduce($part,"myfunction"),"+");
				echo $newPart;
			}
		}


		echo "<div id='dictlist'>";
		foreach($dict_list as $x=>$x_value) {
		  echo "<div><a href='#ref_dict_$x'>$x_value</a></div>";
		}
		echo "<div>";

		$arrWords = countWordInPali($word,true);
		$weight = 0;
		foreach($arrWords as $oneword){
			$weight += $oneword["count"] * $oneword["len"];
		}
		echo "<div>{$_local->gui->word_weight}：$weight {$_local->gui->characters}</div>";
		echo "<div>{$_local->gui->real_declension}：".count($arrWords)." {$_local->gui->forms}</div>";
		foreach($arrWords as $oneword){
			if($oneword["bold"]>0){
				echo "<div><b>{$oneword["word"]}</b> {$oneword["count"]} {$_local->gui->times}</div>";
			}
			else{
				echo "<div>{$oneword["word"]} {$oneword["count"]}{$_local->gui->times}</div>";
			}
		}		
		echo "</div>";
		echo "</div>";
		echo "</div>";
		//参考字典查询结束
		

		//用户词典
		echo "<div id='dict_user' >";	
		echo "<div class='dict_word' ><b>{$_local->gui->undone_function}</b>";
		echo "<div class='' onclick=\"dict_show_edit()\">{$_local->gui->edit}</div>";		
		echo "<div class='pali'>{$word}</div>";

		if($iFetch>0){
			echo "<div id='user_word_edit' style='display:none'>";
		}
		else{
			echo "<div id='user_word_edit'>";
		}
		echo "<fieldset class='broder-1 broder-r'><legend>{$_local->gui->wordtype}</legend>";
		echo "<select id=\"id_type\" name=\"type\" >";
		foreach($_local->type_str as $type){
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
		echo "</div>";			
		//查用户词典结束	

		break;	
}


?>