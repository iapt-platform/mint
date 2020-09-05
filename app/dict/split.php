<?php
//强力拆分复合词
require_once '../public/casesuf.inc';
require_once '../studio/dict_find_un.inc';
require_once '../studio/sandhi.php';
require_once "../path.php";
require_once "../public/_pdo.php";

if(isset($_POST["word"])){
	$input_word=mb_strtolower(trim($_POST["word"]),'UTF-8');
	if(trim($input_word)==""){
		echo "Empty";
	return;
	}
	$arrWords = str_getcsv($input_word,"\n");//支持批量拆分
}
else{
?>
<!--debug only-->
<form action="split.php" method="post">
Words: <textarea type="text" name="word"></textarea>
<input name="debug" type="hidden" />批量查询，单词之间用英文逗号分隔。
<div>
<input type="checkbox" name = "express" checked /> 快速搜索（遇到第一个连音规则成功就返回）
</div>
<input type="submit">
</form>

<?php
	return;
}

if(isset($_POST["express"])){
	if($_POST["express"]==="on"){
		$_express = true;
	}
	else{
		$_express = false;
	}
}
else{
	$_express = false;
}
global $dbh;
$dns = "sqlite:"._FILE_DB_PART_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

global $path;
global $confidence;
global $result;
global $part ;
$part= array();

$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);
$path[]=array("",0);

global $sandhi ;
	//sandhi 语尾表
	$sandhi[]=array("a"=>"","b"=>"","c"=>"","len"=>0,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"a","c"=>"ā","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"ā","b"=>"ā","c"=>"ā","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"ā","c"=>"ā","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"ā","b"=>"a","c"=>"ā","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"e","c"=>"e","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"i","c"=>"i","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"o","c"=>"o","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"u","c"=>"o","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"u","b"=>"a","c"=>"o","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"u","b"=>"u","c"=>"ū","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"u","c"=>"u","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"ī","c"=>"ī","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"ū","c"=>"ū","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"i","c"=>"e","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"i","b"=>"i","c"=>"ī","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"i","b"=>"e","c"=>"e","len"=>1,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"i","b"=>"a","c"=>"ya","len"=>2,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"atth","c"=>"atth","len"=>4,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"taṃ","b"=>"n","c"=>"tann","len"=>4,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"api","c"=>"mpi","len"=>3,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"eva","c"=>"meva","len"=>4,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[o]","b"=>"iva","c"=>"ova","len"=>3,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"ādi","c"=>"ādi","len"=>3,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a[ānaṃ]","b"=>"a","c"=>"ānama","len"=>5,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"a","b"=>"iti","c"=>"āti","len"=>3,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"ca","c"=>"ñca","len"=>3,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"iti","c"=>"nti","len"=>3,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"a","c"=>"ma","len"=>2,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"ā","c"=>"mā","len"=>2,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"u","c"=>"mu","len"=>2,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"[ṃ]","b"=>"h","c"=>"ñh","len"=>2,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"ā","b"=>"[ṃ]","c"=>"am","len"=>2,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"ī","b"=>"[ṃ]","c"=>"im","len"=>2,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"ati","b"=>"tabba","c"=>"atabba","len"=>6,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"ati","b"=>"tabba","c"=>"itabba","len"=>6,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"iti","b"=>"a","c"=>"icca","len"=>4,"adj_len"=>0,"advance"=>false);

/*
	$sandhi[]=array("a"=>"u[ūnaṃ]","b"=>"a","c"=>"ūnama","len"=>5,"adj_len"=>0,"advance"=>false);
	$sandhi[]=array("a"=>"ī[īnaṃ]","b"=>"a","c"=>"īnama","len"=>5,"adj_len"=>0,"advance"=>false);

$sandhi[]=array("a"=>"ā","b"=>"iti","c"=>"āti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"a","b"=>"iti","c"=>"āti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"e","b"=>"iti","c"=>"eti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"ī","b"=>"iti","c"=>"īti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"i","b"=>"iti","c"=>"īti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"o","b"=>"iti","c"=>"oti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"ū","b"=>"iti","c"=>"ūti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"u","b"=>"iti","c"=>"ūti","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"ṃ","b"=>"iti","c"=>"nti","len"=>3,"adj_len"=>0);

$sandhi[]=array("a"=>"ṃ","b"=>"ca","c"=>"ñca","len"=>3,"adj_len"=>0);

$sandhi[]=array("a"=>"a","b"=>"eva","c"=>"eva","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"ā","b"=>"eva","c"=>"āyeva","len"=>5,"adj_len"=>0);
$sandhi[]=array("a"=>"e","b"=>"eva","c"=>"eva","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"i","b"=>"eva","c"=>"yeva","len"=>4,"adj_len"=>0);
$sandhi[]=array("a"=>"ī","b"=>"eva","c"=>"iyeva","len"=>5,"adj_len"=>0);
$sandhi[]=array("a"=>"ī","b"=>"eva","c"=>"īyeva","len"=>5,"adj_len"=>0);
$sandhi[]=array("a"=>"o","b"=>"eva","c"=>"ova","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"u","b"=>"eva","c"=>"veva","len"=>3,"adj_len"=>0);

$sandhi[]=array("a"=>"a","b"=>"eva","c"=>"evā","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"e","b"=>"eva","c"=>"evā","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"i","b"=>"eva","c"=>"yevā","len"=>4,"adj_len"=>0);
$sandhi[]=array("a"=>"ī","b"=>"eva","c"=>"yevā","len"=>4,"adj_len"=>0);
$sandhi[]=array("a"=>"ī","b"=>"eva","c"=>"iyevā","len"=>4,"adj_len"=>0);
$sandhi[]=array("a"=>"ī","b"=>"eva","c"=>"īyevā","len"=>4,"adj_len"=>0);
$sandhi[]=array("a"=>"o","b"=>"eva","c"=>"ovā","len"=>4,"adj_len"=>0);

$sandhi[]=array("a"=>"ā","b"=>"api","c"=>"āpi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"a","b"=>"api","c"=>"āpi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"e","b"=>"api","c"=>"epi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"ī","b"=>"api","c"=>"īpi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"i","b"=>"api","c"=>"īpi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"o","b"=>"api","c"=>"opi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"ū","b"=>"api","c"=>"ūpi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"u","b"=>"api","c"=>"ūpi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"u","b"=>"api","c"=>"upi","len"=>3,"adj_len"=>0);
$sandhi[]=array("a"=>"ṃ","b"=>"api","c"=>"mpi","len"=>3,"adj_len"=>0);
*/
	//$sandhi[]=array("a"=>"a","b"=>"a","c"=>"a","len"=>1,"adj_len"=>-1,"advance"=>true);
	//$sandhi[]=array("a"=>"ī","b"=>"","c"=>"i","len"=>1,"adj_len"=>0,"advance"=>true);



$search  = array('aa', 'ae', 'ai', 'ao', 'au', 'aā', 'aī', 'aū', 'ea', 'ee', 'ei', 'eo', 'eu', 'eā', 'eī', 'eū', 'ia', 'ie', 'ii', 'io', 'iu', 'iā', 'iī', 'iū', 'oa', 'oe', 'oi', 'oo', 'ou', 'oā', 'oī', 'oū', 'ua', 'ue', 'ui', 'uo', 'uu', 'uā', 'uī', 'uū', 'āa', 'āe', 'āi', 'āo', 'āu', 'āā', 'āī', 'āū', 'īa', 'īe', 'īi', 'īo', 'īu', 'īā', 'īī', 'īū', 'ūa', 'ūe', 'ūi', 'ūo', 'ūu', 'ūā', 'ūī', 'ūū');
$replace = array('a-a', 'a-e', 'a-i', 'a-o', 'a-u', 'a-ā', 'a-ī', 'a-ū', 'e-a', 'e-e', 'e-i', 'e-o', 'e-u', 'e-ā', 'e-ī', 'e-ū', 'i-a', 'i-e', 'i-i', 'i-o', 'i-u', 'i-ā', 'i-ī', 'i-ū', 'o-a', 'o-e', 'o-i', 'o-o', 'o-u', 'o-ā', 'o-ī', 'o-ū', 'u-a', 'u-e', 'u-i', 'u-o', 'u-u', 'u-ā', 'u-ī', 'u-ū', 'ā-a', 'ā-e', 'ā-i', 'ā-o', 'ā-u', 'ā-ā', 'ā-ī', 'ā-ū', 'ī-a', 'ī-e', 'ī-i', 'ī-o', 'ī-u', 'ī-ā', 'ī-ī', 'ī-ū', 'ū-a', 'ū-e', 'ū-i', 'ū-o', 'ū-u', 'ū-ā', 'ū-ī', 'ū-ū');

$allword = array();
foreach($arrWords as $oneword){
	//预处理
	//将双元音拆开
	$word = str_replace($search, $replace, $oneword);

	if(isset($_POST["debug"])){
		echo "Look up：{$word}<br>";
	}
	
	//按连字符拆开处理
	$arrword = str_getcsv($word,"-");

	$t1=microtime_float();
	$output = array();
	foreach($arrword as $oneword){
		$result = array();//全局变量，递归程序的输出容器

		if(mb_strlen($oneword,"UTF-8")<30){
			mySplit2($oneword,0,$_express);
		}
		else{
			mySplit2($oneword,0,$_express);
		}
		
		arsort($result);//按信心指数排序
		$wordlist = array();
		$iMax = 5;
		$iCount = 0;
		foreach($result as $row=>$value){
			$iCount++;
			$word_part  = array();
			$word_part["word"] = $row;
			$word_part["confidence"] = $value;
			$wordlist[] = $word_part;
			if($iCount>=$iMax){
			break;
			}

		}
		$output[] = $wordlist;

		if(isset($_POST["debug"])){
			echo "<h2>{$oneword}</h2>";
			echo "<h4>".count($result)."</h4>";
		}
		$iCount=0;
		foreach($result as $row=>$value){
			if($iCount>10){
				break;
			}
			$iCount++;
			$level=$value*90;
			if(isset($_POST["debug"])){
				echo $row."-[".$value."]<br>";
			}
		}
		
		/*
		后处理
		-ssāpi=-[ssa]-api
		*/
	}
	$t2 = microtime_float();
	$one_split["data"]=$output;
	$one_split["time"]= $auto_split_times;
	$one_split["second"]= $t2-$t1;	
	$allword[] = $one_split;

	if(isset($_POST["debug"])){
		echo "<div>";
		echo "<br>查询【{$auto_split_times}】次";	
		echo "time:".($t2-$t1);
		echo "</div>";
	}
}

if(isset($_POST["debug"])){
	echo "<pre style='margin:2em;padding:1em;background-color:#e9e9e9;'>";
	print_r($allword);
	echo "</pre>";
}
echo json_encode($allword,JSON_UNESCAPED_UNICODE);

/*
用于数组连接字符串
*/
function myfunction($v1,$v2)
{
	return $v1 . "+" . $v2;
}
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

function dict_lookup($word){
	global $dbh;
	$query = "select weight from part where \"word\" = ? ";
	$stmt = $dbh->prepare($query);
	$stmt->execute(array($word));
    $row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        return $row[0];
    } else {
        return 0;
    }	
}

/*
查找某个单词是否在现有词典出现
返回信心指数
*/
function isExsit($word,$adj_len=0){

	global $auto_split_times;
	global $result;
	global $part;
	global $confidence;
	$auto_split_times++;
	
	if(isset($_POST["debug"])){
		echo "<div>正在查询：{$word}</div>";
	}
	$isFound=false;
	if(isset($part["{$word}"]))
	{
		if($part["{$word}"]>0){
			$isFound=true;
			$count=$part["{$word}"]+1;			
		}
	}
	else{
		$db=dict_lookup($word);
		//加入查询缓存
		$part["{$word}"] = $db;
		if($db>0){
			$isFound=true;
			$count=$db+1;
		}
		else{
			
		}
	} 

	if($isFound)
	{
		if(isset($confidence["{$word}"])){
			$cf=$confidence["{$word}"];
		}
		else{
			$len=mb_strlen($word,"UTF-8")+$adj_len;
			$len_correct=1.2;
			$count2=1.1+pow($count,1.18);
			$conf_num=pow(1/$count2,pow(($len-1),$len_correct));
			$cf=round(1/(1+640*$conf_num),9);

			$confidence["{$word}"]=$cf;
		}
		return($cf);
		
	}
	else{
		return(-1);
	}
}

/*
核心拆分函数

$strWord, 要查询的词
$deep, 当前递归深度
$express=true, 快速查询
$adj_len=0 长度校正系数
$c_threshhold 信心指数阈值
*/

function mySplit2($strWord,$deep,$express=false,$adj_len=0,$c_threshhold=0.8){
	global $path;
	global $result;
	global $sandhi ;
	$output = array();
	
	//达到最大搜索深度，返回
	if($deep>=16){
		$word = "";
		$cf=1.0;
		for($i=0;$i<$deep;$i++){
			$word .= $path[$i][0];
			if(isset($_POST["debug"])){
				$word .="(".$path[$i][1].")-";
			}
			else{
				$word .= "-";
			}
			$cf=$cf*$path[$i][1];
		}
		$len=pow(mb_strlen($strWord,"UTF-8"),3);
		$cf+=(0-$len)/($len+150);
		$word .= "{$strWord}";
		$result[$word]=$cf;
		return;
	}
	//直接找到
	$confidence=isExsit($strWord,$adj_len);
	if($confidence>=0){
		$output[] = array($strWord,"",$confidence);
	}
	else{
		$confidence=isExsit("[".$strWord."]");
		if($confidence>=0){
			$output[] = array("[".$strWord."]","",$confidence);
		}
	}

	//如果开头有双辅音，去掉第一个辅音。因为巴利语中没有以双辅音开头的单词。
	$doubleword="kkggccjjṭṭḍḍttddppbb";
	if(mb_strlen($strWord,"UTF-8")>2){
		$left2=mb_substr($strWord,0,2,"UTF-8");
		if(mb_strpos($doubleword,$left2,0,"UTF-8")!==FALSE){
			$strWord=mb_substr($strWord,1,NULL,"UTF-8");
		}
	}


	$len=mb_strlen($strWord,"UTF-8");
	if($len>2){
		for($i=$len;$i>1;$i--){
			foreach($sandhi as $row){
				if(mb_substr($strWord,$i-$row["len"],$row["len"],"UTF-8")==$row["c"]){
					$str1=mb_substr($strWord,0,$i-$row["len"],"UTF-8").$row["a"];
					$str2=$row["b"].mb_substr($strWord,$i,NULL,"UTF-8");
					$confidence=isExsit($str1,$adj_len);
					if($confidence >= $c_threshhold){
						$output[] = array($str1,$str2,$confidence,$row["adj_len"]);
						if($express){
							break;
						}
					}

				}
			}
		}
	}
	if(count($output)>0){
		foreach($output as $part){
			$path[$deep][0]=$part[0];
			$path[$deep][1]=$part[2];
			if($part[1]!=""){
				mySplit2($part[1],($deep+1),$express,$part[3],$c_threshhold);
			}
			else{
				$word = "";
				$cf=1.0;
				for($i=0;$i<$deep;$i++){
					$word .= $path[$i][0]."+";
					if(isset($_POST["debug"])){
						$word .= "(".$path[$i][1].")-";
					}
					$cf=$cf*$path[$i][1];
				}
				$word .= $part[0];
				if(isset($_POST["debug"])){
					$word .= "({$part[2]})";
				}
				$cf=$cf+$part[2]*0.1;
				if($cf >= $c_threshhold){
					$result[$word]=$cf;
				}
			}
		}
	}
	else{
		$word = "";
		$cf=1.0;
		for($i=0;$i<$deep;$i++){
			$word .= $path[$i][0]."+";
			if(isset($_POST["debug"])){
				$word .= "(".$path[$i][1].")-";
			}
			$cf=$cf*$path[$i][1];
		}
		$len=pow(mb_strlen($strWord,"UTF-8"),3);
		$cf+=(0-$len)/($len+150);
		if(isset($_POST["debug"])){
			$word .= $strWord."(0)";
		}
		else{
			$word .= $strWord;
		}
		
		if($cf >= $c_threshhold){
			$result[$word]=$cf;
		}
	}
}



?>