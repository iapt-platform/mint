<?php
require 'checklogin.inc';
include "../public/config.php";
include "../public/_pdo.php";
include "../public/function.php";

if(isset($_GET["book"])){
	$in_book=$_GET["book"];
}
if(isset($_GET["para"])){
	$in_para=$_GET["para"];
}
	$para_list=str_getcsv($in_para);
	$strQueryPara="(";//单词查询字串
	foreach($para_list as $para){
		$strQueryPara.="'{$para}',";
	}
	$strQueryPara=mb_substr($strQueryPara, 0,mb_strlen($strQueryPara,"UTF-8")-1,"UTF-8");
	$strQueryPara.=")";

if(isset($_GET["debug"])){
	$debug=true;;
}
else{
	$debug=false;
}




function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

$time_start = microtime_float();

$dict_file_user=$dir_user_base.$userid.$dir_dict_user.'/';
$dict_file_sys=$dir_dict_system;
$dict_file_third=$dir_dict_3rd;

//open database

global $PDO;


	//查询单词表
	$db_file = "{$dir_palicanon}templet/p".$in_book."_tpl.db3";
	
	PDO_Connect("sqlite:$db_file");
	$query="SELECT paragraph,vri,real FROM \"main\" WHERE (\"paragraph\" in ".$strQueryPara." ) and \"real\"<>\"\" and \"type\"<>'.ctl.' ";
	if($debug){
		echo "filename:".$db_file."<br>";
		echo $query."<br>";
	}
	$FetchAllWord = PDO_FetchAll($query);
	$iFetch=count($FetchAllWord);
	if($iFetch==0){
		echo json_encode(array(), JSON_UNESCAPED_UNICODE);
		exit;
	}
	$voc_list=array();

	foreach($FetchAllWord as $word){
		$voc_list[$word["real"]]=1;
	}
	if($debug){
		echo "单词表共计：".count($voc_list)."词<br>";
	}

//查询单词表结束

	$word_list=array();
	foreach($voc_list as $word=>$value){
		array_push($word_list,$word);
	}
$lookup_loop=2;

$dict_word_spell=array();
$output=array();
$db_file_list=array();
//用户词典
array_push($db_file_list , array($_file_db_wbw," ORDER BY rowid DESC"));

array_push($db_file_list , array($dict_file_sys."sys_regular.db"," ORDER BY confidence DESC"));
array_push($db_file_list , array($dict_file_sys."sys_irregular.db",""));
array_push($db_file_list , array($dict_file_sys."union.db",""));
array_push($db_file_list , array($dict_file_sys."comp.db",""));

array_push($db_file_list , array($dict_file_third."pm.db",""));
array_push($db_file_list , array($dict_file_third."bhmf.db",""));
array_push($db_file_list , array($dict_file_third."shuihan.db",""));
array_push($db_file_list , array($dict_file_third."concise.db",""));
array_push($db_file_list , array($dict_file_third."uhan_en.db",""));


for($i=0;$i<$lookup_loop;$i++)
{
	$parent_list=array();
	$strQueryWord="(";//单词查询字串
	foreach($word_list as $word){
		$word=str_replace("'","’",$word);
		$strQueryWord.="'{$word}',";
	}
	$strQueryWord=mb_substr($strQueryWord, 0,mb_strlen($strQueryWord,"UTF-8")-1,"UTF-8");
	$strQueryWord.=")";
	if($debug){
		echo "<h2>第{$i}轮查询：$strQueryWord</h2>";
	}
	foreach($db_file_list as $db){
		$db_file=$db[0];
		$db_sort=$db[1];
		if($debug){
			echo "dict:$db_file<br>";
		}
		PDO_Connect("sqlite:{$db_file}");	
		PDO_Execute("PRAGMA synchronous = OFF");
		PDO_Execute("PRAGMA journal_mode = WAL");
		PDO_Execute("PRAGMA foreign_keys = ON");
		PDO_Execute("PRAGMA busy_timeout = 5000");

		$strOrderby=$db[1];

		
		if($i==0){
			$query = "select * from dict where \"pali\" in {$strQueryWord} AND ( type <> '.n:base.' AND  type <> '.ti:base.' AND  type <> '.adj:base.'  AND  type <> '.pron:base.'  AND  type <> '.v:base.'  AND  type <> '.part.' ) ".$strOrderby;
		}
		else{
			$query = "select * from dict where  \"pali\" in {$strQueryWord}  ".$strOrderby;
		}

		if($debug){
			echo $query."<br>";
		}
		try {
			$Fetch = PDO_FetchAll($query);
		} catch (Exception $e) {
			if($debug){
				echo 'Caught exception: ',  $e->getMessage(), "\n";
			}
			continue;
		}
		
		$iFetch=count($Fetch);
		if($debug){
			echo "count:{$iFetch}<br>";
		}
		if($iFetch>0){
			foreach($Fetch as $one){
				$id = $one["id"];
				if(isset($one["guid"])){
					$guid = $one["guid"];
				}
				else{
					$guid = "";
				}
				$pali = $one["pali"];
				$dict_word_spell["{$pali}"]=1;
				$type = $one["type"];
				$gramma = $one["gramma"];
				$parent = $one["parent"];
				$mean = $one["mean"];
				
				if(isset($one["note"])){
					$note = $one["note"];
				}
				else{
					$note = "";
				}
				
				if(isset($one["parts"])){
					$parts = $one["parts"];
				}
				else if(isset($one["factors"])){
					$parts = $one["factors"];
				}
				else{
					$parts = "";
				}
				
				if(isset($one["partmean"])){
					$partmean = $one["partmean"];
				}
				else if(isset($one["factormean"])){
					$partmean = $one["factormean"];
				}
				else{
					$partmean = "";
				}
				
				if(isset($one["part_id"])){
					$part_id = $one["part_id"];
				}
				else{
					$part_id = "";
				}

				if(isset($one["status"])){
					$status = $one["status"];
				}
				else{
					$status = "";
				}

				if(isset($one["dict_name"])){
					$dict_name = $one["dict_name"];
				}
				else{
					$dict_name = "";
				}

				if(isset($one["language"])){
					$language = $one["language"];
				}
				else{
					$language = "en";
				}
				
				array_push($output,array(
										"id"=>$id,
										"guid"=>$guid,
										"pali"=>$pali,
										"type"=>$type,
										"gramma"=>$gramma,
										"parent"=>$parent,
										"mean"=>$mean,
										"note"=>$note,
										"parts"=>$parts,
										"part_id"=>$part_id,
										"partmean"=>$partmean,
										"status"=>$status,
										"dict_name"=>$dict_name,
										"language"=>$language
										));
				if(!empty($parent)){
					if($pali != $parent){
						$parent_list[$one["parent"]]=1;
					}
				}
				if($type!="part"){
					if(isset($one["factors"])){
						$parts=str_getcsv($one["factors"],'+');
						foreach($parts as $x){
							if(!empty($x)){
								if($x != $pali){
									$parent_list[$x]=1;
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
	
	if($debug){
		echo "parent:".count($parent_list)."<br>";
		//print_r($parent_list)."<br>";
	}
	if(count($parent_list)==0){
		break;
	}
	else{
		$word_list=array();
		foreach($parent_list as $x=>$value){
			array_push($word_list,$x);
		}
	}
	
}
//查询结束
//删除无效数据
$newOutput = array();
foreach($output as $value){
	
	if($value["dict_name"]=="auto"){
		if(isset($dict_word_spell["{$value["parent"]}"])){
			array_push($newOutput,$value);
		}
	}
	
	else
	{
		array_push($newOutput,$value);
	}
}


if($debug){
	echo "<textarea width=\"100%\" >";

echo json_encode($newOutput, JSON_UNESCAPED_UNICODE);

	echo "</textarea>";
}

if($debug){
	echo "生成：".count($output)."<br>";
	echo "有效：".count($newOutput)."<br>";
	
}
//开始匹配
$counter=0;
$output=array();
foreach($FetchAllWord as $word){
	$pali=$word["real"];
	$type="";
	$gramma="";
	$mean="";
	$parent="";
	$parts="";
	$partmean="";
	foreach($newOutput as $dictword){
		if($dictword["pali"]==$pali){
			if($type=="" && $gramma==""){
				$type=$dictword["type"];
				$gramma=$dictword["gramma"];
			}
			if(trim($mean)=="" ){
				$mean=str_getcsv($dictword["mean"],"$")[0];
				
			}
			if($parent=="" ){
				$parent=$dictword["parent"];
			}
			if($parts=="" ){
				$parts=$dictword["parts"];
			}
			if($partmean==""){
				$partmean=$dictword["partmean"];
			}
		}

	}
	if($mean=="" && $parent!=""){
		foreach($newOutput as $parentword){
			if($parentword["pali"]==$parent){
				if($parentword["mean"]!=""){
					$mean=trim(str_getcsv($parentword["mean"],"$")[0]);
					if($mean!=""){
						break;
					}
				}
			}
		}
	}
	if(	$type!="" || 
	$gramma!="" || 
	$mean!="" || 
	$parent!="" || 
	$parts!="" || 
	$partmean!=""){
		$counter++;
	}
	array_push($output,
			array("book"=>$in_book,
				 "paragraph"=>$word["paragraph"],
				 "num"=>$word["vri"],
			     "pali"=>$word["real"],
				 "type"=>$type,
				 "gramma"=>$gramma,
				 "mean"=>$mean,
				 "parent"=>$parent,							 
				 "parts"=>$parts,
				 "partmean"=>$partmean,
				 "status"=>3
				 ));
}
if($debug){
	echo "<textarea width=\"100%\" >";
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
if($debug){
	echo "</textarea>";
}
if($debug){
	echo "匹配".(($counter/count($FetchAllWord))*100)."<br>";
	foreach($output as $result){
		//echo "{$result["pali"]}-{$result["mean"]}-{$result["parent"]}<br>";
	}
	$queryTime=(microtime_float()-$time_start)*1000;
	echo "<div >搜索时间：$queryTime 毫秒</div>";
}

?>