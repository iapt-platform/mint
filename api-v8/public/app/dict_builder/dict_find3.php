<?php
//查询参考字典
require_once 'casesuf.inc';
require_once "../config.php";
require_once "./_pdo.php";


$op=$_GET["op"];
$word=mb_strtolower($_GET["word"],'UTF-8');
$org_word=$word;

$count_return=0;
$dict_list=array();

global $PDO;
PDO_Connect(_FILE_DB_REF_);

function isExsit($word){
global $PDO;
		$query = "SELECT count(*) as co from dict where \"word\" = ?";
		$row=PDO_FetchOne($query,array($word));

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
		echo "<wordlist>";
		$query = "SELECT word,count(*) as co from dict where \"eword\" like ? OR \"word\" like ? group by word order by length limit 0,100";
		$Fetch = PDO_FetchAll($query,array($word.'%',$word.'%'));
		$iFetch=count($Fetch);
		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				$outXml = "<word>";
				$word=$Fetch[$i]["word"];
				$outXml = $outXml."<pali>$word</pali>";
				$outXml = $outXml."<count>".$Fetch[$i]["co"]."</count>";
				$outXml = $outXml."</word>";
				echo $outXml;
			}
		}
		echo "</wordlist>";
		break;
	case "search":
		//直接查询
		$query = "SELECT dict.dict_id,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"word\" = ? limit 0,30";
		
		$Fetch = PDO_FetchAll($query,array($word));
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
				$query = "SELECT dict.dict_id,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"word\" = ? limit 0,30";
				$Fetch = PDO_FetchAll($query,array($x));
				$iFetch=count($Fetch);
				$count_return+=$iFetch;
				if($iFetch>0){
					echo $x . ":<br />$x_value<br>";
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
			}
		}
		//去除尾查结束
		
		//模糊查
		//模糊查结束
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
			$query = "SELECT dict.dict_id,dict.word,dict.mean,info.shortname from dict LEFT JOIN info ON dict.dict_id = info.id where \"mean\" like ? limit 0,30";
			$Fetch = PDO_FetchAll($query,array($wordInMean));
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
					$outXml = "<div class='dict_word'>";
					$outXml = $outXml."<div class='word'>".$Fetch[$i]["word"]."</div>";
					$outXml = $outXml."<div class='dict'>".$Fetch[$i]["shortname"]."</div>";
					$outXml = $outXml."<div class='mean'>".$heigh_light_mean."</div>";
					$outXml = $outXml."</div>";
					echo $outXml;
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
		  echo "<a href='#ref_dict_$x'>$x_value</a>";
		}
		echo "</div>";
		
		break;		
}


?>