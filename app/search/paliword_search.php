<?php
//全文搜索
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
//$word=$_GET["word"];
$org_word=$word;
$arrWordList = str_getcsv($word," ");

$count_return=0;
$dict_list=array();

global $PDO;
function microtime_float()
{
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
}

switch($op){
	case "pre"://预查询
	{
		$time_start = microtime_float();
		
		$searching=$arrWordList[count($arrWordList)-1];
		$dbfile = _FILE_DB_WORD_INDEX_;
		PDO_Connect("sqlite:".$dbfile);
		
		if(count($arrWordList)>1){
			echo "<div>";
			foreach($arrWordList as $oneword){
				echo $oneword."+";
			}
			echo "</div>";
		}

		$query = "select word,count from wordindex where \"word_en\" like ".$PDO->quote($searching.'%')." OR \"word\" like ".$PDO->quote($searching.'%')." limit 0,20";
		$Fetch = PDO_FetchAll($query);
		$queryTime=(microtime_float()-$time_start)*1000;

		$iFetch=count($Fetch);
		if($iFetch>0){
			for($i=0;$i<$iFetch;$i++){
				$word=$Fetch[$i]["word"];
				$count=$Fetch[$i]["count"];

				echo  "<a href='paliword.php?key={$word}'>$word-$count</a>";

			}
		}

		break;
	}
	case "search":
	{
		if(count($arrWordList)>1){
			$strQuery="";
			foreach($arrWordList as $oneword){
				$strQuery.="\"text\" like \"% {$oneword} %\" AND";
			}
			$strQuery = substr($strQuery,0,-3);
			$dictFileName=_FILE_DB_PALITEXT_;
			PDO_Connect("sqlite:$dictFileName");
			$query = "SELECT book,paragraph, html FROM pali_text WHERE {$strQuery}  LIMIT 0,20";
			$Fetch = PDO_FetchAll($query);
			echo "<div>$query</div>";
			$iFetch=count($Fetch);
			foreach($Fetch as $row){
				$html = $row["html"];
				foreach($arrWordList as $oneword){
					$html=str_replace($oneword,"<hl>{$oneword}</hl>",$html);
				}
				echo "<div class='dict_word'>{$html}</div>";
			}
			return;
		}
		
	//计算某词在三藏中出现的次数		
		$time_start = microtime_float();
		$arrRealWordList = countWordInPali($word);
		$countWord=count($arrRealWordList);
		if($countWord==0){
			echo "<p>没有查到。可能是拼写有问题。</p>";
			exit;
		}
		$strQueryWordId="(";//实际出现的单词id查询字串
		$aQueryWordList=array();//id 为键 拼写为值的数组
		$aShowWordList=array();//拼写为键 个数为值的数组
		$aShowWordIdList=array();//拼写为键 值Id的数组
		for($i=0;$i<$countWord;$i++){
			$value= $arrRealWordList[$i];
			$strQueryWordId.="'{$value["id"]}',";
			$aQueryWordList["{$value["id"]}"]=$value["word"];
			$aShowWordList[$value["word"]]=$value["count"];
			$aShowWordIdList[$value["word"]]=$value["id"];
		}
		$strQueryWordId=mb_substr($strQueryWordId, 0,mb_strlen($strQueryWordId,"UTF-8")-1,"UTF-8");
		$strQueryWordId.=")";
		
		$queryTime=(microtime_float()-$time_start)*1000;

		echo "<div id=\"dict_bold\">";	
		//主显示区开始
		echo "<div style='display:flex;'>";
		
		//主显示区左侧开始
		echo "<div style='flex:3;max-width: 17em;min-width: 10em;border-right: 1px solid var(--border-line-color);text-align: right;padding-right: 1em;'>";


		echo "<button onclick=\"dict_update_bold(0)\">筛选</button>";
				
		echo "<div>共{$countWord}单词符合</div>";		
		
		//显示单词列表
		echo "<div>";
		echo "<input id='bold_all_word' type='checkbox' checked='true' value='' onclick=\"dict_bold_word_all_select()\"/>全选<br />";
		arsort($aShowWordList);
		$i=0;
		foreach($aShowWordList as $x=>$x_value) {
			$wordid=$aShowWordIdList[$x];
			echo "<a onclick=\"dict_bold_word_select({$i})\">";
			echo $x.":".$x_value;
			echo "</a>";
			echo "<input id='bold_word_{$i}' type='checkbox' checked value='{$wordid}' />";			
			echo "<br />";
			$i++;
		}

		echo "<input id='bold_word_count' type='hidden' value='{$countWord}' />";
		echo "</div>";
		
		//查找这些词出现在哪些书中
		echo "<div id='bold_book_list'>";
		$booklist=render_book_list($strQueryWordId);
		echo "</div>";
		
		$wordInBookCounter=0;
		$strFirstBookList="(";
		foreach($booklist as $onebook){
			$wordInBookCounter+=$onebook[1];
			$strFirstBookList.="'".$onebook[0]."',";
			if($wordInBookCounter>=20){
				break;
			}
		}
		$strFirstBookList=mb_substr($strFirstBookList, 0,mb_strlen($strFirstBookList,"UTF-8")-1,"UTF-8");
		$strFirstBookList.=")";		
		
		echo "</div>";
		//黑体字主显示区左侧结束
		
		//黑体字主显示区右侧开始
		echo "<div id=\"dict_bold_right\" style='flex:7;padding-left:1em;'>";
		//前20条记录
		$time_start=microtime_float();
		$dictFileName=_FILE_DB_PALI_INDEX_;
		PDO_Connect("sqlite:$dictFileName");
		$query = "SELECT book,paragraph, wordindex FROM word WHERE \"wordindex\" in $strQueryWordId and book in $strFirstBookList group by book,paragraph LIMIT 0,20";
		$Fetch = PDO_FetchAll($query);
		//echo "<div>$query</div>";
		$queryTime=(microtime_float()-$time_start)*1000;
		$iFetch=count($Fetch);
		if($iFetch>0){
			$dictFileName=_FILE_DB_PALITEXT_;
			PDO_Connect("sqlite:$dictFileName");			
			for($i=0;$i<$iFetch;$i++){
				$paliwordid=$Fetch[$i]["wordindex"];
				$paliword=$aQueryWordList["{$paliwordid}"];
				$book=$Fetch[$i]["book"];
				$paragraph=$Fetch[$i]["paragraph"];		
				$bookInfo = _get_book_info($book);
				$bookname=$bookInfo->title;
				$c1=$bookInfo->c1;
				$c2=$bookInfo->c2;
				$c3=$bookInfo->c3;

				echo "<div class='dict_word' style='margin: 10px 0;padding: 5px;border-bottom: 1px solid var(--border-line-color);'>";
				echo  "<div style='font-size: 130%;font-weight: 700;'>$paliword</div>";
				//echo "<div class='dict_word'>";
				$path_1 = $c1.">";
				if($c2 !== ""){
					$path_1=$path_1.$c2.">";
				}
				if($c3 !== ""){
					$path_1=$path_1.$c3.">";
				}
				$path_1=$path_1."《{$bookname}》>";
				$query = "select * from pali_text where \"book\" = '{$book}' and \"paragraph\" = '{$paragraph}' limit 0,1";
				$FetchPaliText = PDO_FetchAll($query);
				$countPaliText=count($FetchPaliText);
				if($countPaliText>0){
					$path="";
					$parent = $FetchPaliText[0]["parent"];
					$deep=0;
					$sFirstParentTitle="";
					//循环查找父标题 得到整条路径
					while($parent>-1){
						$query = "select * from pali_text where \"book\" = '{$book}' and \"paragraph\" = '{$parent}' limit 0,1";
						$FetParent = PDO_FetchAll($query);
						$path="{$FetParent[0]["toc"]}>{$path}";
						if($sFirstParentTitle==""){
							$sFirstParentTitle = $FetParent[0]["toc"];
						}						
						$parent = $FetParent[0]["parent"];
						$deep++;
						if($deep>5){
							break;
						}
					}
					$path=$path_1.$path."para. ".$paragraph;
					echo  "<div class='mean' style='font-size:120%'><a href='../reader/?view=para&book={$book}&para={$paragraph}&display=para' target='_blank'>$path</a></div>";
					
					for($iPali=0;$iPali<$countPaliText;$iPali++){
						if(substr($paliword,-1)=="n"){
							$paliword=substr($paliword,0,-1);
						}
						$htmltext=$FetchPaliText[0]["html"];
						$light_text=str_replace($paliword,"<hl>{$paliword}</hl>",$htmltext);
						echo  "<div class='wizard_par_div'>{$light_text}</div>";
					}
					//echo  "<div class='wizard_par_div'>{$light_text}</div>";
					echo  "<div class='search_para_tools'></div>";
					
				}

				echo  "</div>";
			}
		}
		$queryTime=(microtime_float()-$time_start)*1000;
		echo "<div >搜索时间：$queryTime </div>";
		echo "</div>";
		//黑体字主显示区右侧结束
		echo "<div id=\"dict_bold_review\" style='flex:2;'>";	
		
		echo "</div>";

		echo "</div>";
		//黑体字主显示区结束
		echo "</div>";
		//查黑体字结束
		
		echo "<div id='real_dict_tab'></div>";
		break;
	}
	case "update":
		$target=$_GET["target"];
		switch($target){
			case "bold";
				$wordlist=$_GET["wordlist"];
				$booklist=$_GET["booklist"];
				$aBookList=ltrim($booklist,"(");
				$aBookList=rtrim($aBookList,")");
				$aBookList=str_replace("'","",$aBookList);
				$aBookList=str_getcsv($aBookList);
				$arrBookType=json_decode(file_get_contents("../public/book_name/booktype.json"));
				//查找这些词出现在哪些书中
				$newBookList=render_book_list($wordlist,$aBookList);
				
				//前20条记录
				$time_start=microtime_float();
				$dictFileName=_FILE_DB_PALI_INDEX_;
				PDO_Connect("sqlite:$dictFileName");

				$query = "select * from word where \"wordindex\" in $wordlist and \"book\" in $booklist group by book,paragraph  limit 0,20";
				$Fetch = PDO_FetchAll($query);
				$queryTime=(microtime_float()-$time_start)*1000;
				//echo "<div >搜索时间：$queryTime </div>";
				if($booklist=="()"){
					echo "<div >请选择书名</div>";
				}
				$iFetch=count($Fetch);
				if($iFetch>0){
					$dictFileName=_FILE_DB_PALITEXT_;
					PDO_Connect("sqlite:$dictFileName");
					for($i=0;$i<$iFetch;$i++){
						$paliword=$Fetch[$i]["wordindex"];
						//$paliword=$wordlist["{$paliwordid}"];
						
						$book=$Fetch[$i]["book"];
						$bookInfo = _get_book_info($book);
						$bookname=$bookInfo->title;
						$c1=$bookInfo->c1;
						$c2=$bookInfo->c2;
						$c3=$bookInfo->c3;
						$paragraph=$Fetch[$i]["paragraph"];

						$path_1 = $c1.">";
						if($c2 !== ""){
							$path_1=$path_1.$c2.">";
						}
						if($c3 !== ""){
							$path_1=$path_1.$c3.">";
						}
						$path_1=$path_1."《{$bookname}》>";

						echo "<div class='dict_word'>";
						echo  "<div class='book' ><span style='font-size:110%;font-weight:700;'>《{$bookname}》</span> <tag>$c1</tag> <tag>$c2</tag> </div>";
						echo  "<div class='mean'>$paliword</div>";

						$query = "select * from pali_text where \"book\" = '{$book}' and \"paragraph\" = '{$paragraph}' limit 0,20";
						$FetchPaliText = PDO_FetchAll($query);
						$countPaliText=count($FetchPaliText);
						if($countPaliText>0){
							for($iPali=0;$iPali<$countPaliText;$iPali++){
								$path="";
								$parent = $FetchPaliText[0]["parent"];
								$deep=0;
								$sFirstParentTitle="";
								while($parent>-1){
									$query = "select * from pali_text where \"book\" = '{$book}' and \"paragraph\" = '{$parent}' limit 0,1";
									$FetParent = PDO_FetchAll($query);
									if($sFirstParentTitle==""){
										$sFirstParentTitle = $FetParent[0]["toc"];
									}	
									$path="{$FetParent[0]["toc"]}>{$path}";
									$parent = $FetParent[0]["parent"];
									$deep++;
									if($deep>5){
										break;
									}
								}
								$path=$path."No. ".$paragraph;
								echo  "<div class='mean' style='font-size:120%;'><a href='../reader/?view=para&book={$book}&para={$paragraph}&display=sent#para_{$paragraph}' target='_blank' >$path</a></div>";
																
								if(substr($paliword,-1)=="n"){
									$paliword=substr($paliword,0,-1);
								}
								$light_text=str_replace($paliword,"<hl>{$paliword}</hl>",$FetchPaliText[$iPali]["html"]);
								echo  "<div class='wizard_par_div'>{$light_text}</div>";
								echo  "<div class='search_para_tools'></div>";

							}
						}

						echo  "</div>";
					}
				}		
				break;
		}
		break;
}


?>