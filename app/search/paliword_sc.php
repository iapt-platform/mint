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
$word=mb_strtolower($_GET["key"],'UTF-8');
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

$result = array();
$result["error"]="";

switch($op){
	case "pre"://预查询
	{
		$time_start = microtime_float();
		
		$searching=$arrWordList[count($arrWordList)-1];
		$dbfile = _FILE_DB_WORD_INDEX_;
		PDO_Connect("sqlite:".$dbfile);
		
		if(count($arrWordList)>1){
			//echo "<div>";
			foreach($arrWordList as $oneword){
				//echo $oneword."+";
			}
			//echo "</div>";
		}

		$query = "SELECT word,count,bold FROM wordindex WHERE word_en  like  ? OR word like ? limit 0,20";
		$Fetch = PDO_FetchAll($query,array("{$searching}%","{$searching}%"));
		echo json_encode($Fetch,JSON_UNESCAPED_UNICODE);
		break;
	}
	case "search":
	{
		$_pagesize = 20;
		if(isset($_GET["page"])){
			$_page = (int)$_GET["page"];
		}
		else{
			$_page=0;
		}
		
		if(count($arrWordList)>1){
			$dictFileName=_FILE_DB_PALITEXT_;
			PDO_Connect("sqlite:$dictFileName");
			# 首先精确匹配
			$words = implode(" ",$arrWordList);
			$query = "SELECT book,paragraph, text FROM pali_text WHERE text like ?  LIMIT ? , ?";
			$Fetch = PDO_FetchAll($query,array("%{$words}%",$_page*$_pagesize,$_pagesize));
			#然后查不精确的
			$strQuery="";
			foreach($arrWordList as $oneword){
				$strQuery.="\"text\" like \"% {$oneword} %\" AND";
			}
			$strQuery = substr($strQuery,0,-3);

			$query = "SELECT book,paragraph, html FROM pali_text WHERE {$strQuery}  LIMIT 0,20";
			$Fetch = PDO_FetchAll($query);

			$iFetch=count($Fetch);
			foreach($Fetch as $row){
				$html = $row["html"];
				foreach($arrWordList as $oneword){
					$html=str_replace($oneword,"<hl>{$oneword}</hl>",$html);
				}
				//echo "<div class='dict_word'>{$html}</div>";
			}

			# 然后查特别不精确的
			return;
		}
		
	//计算某词在三藏中出现的次数		
		$time_start = microtime_float();
		$arrRealWordList = countWordInPali($word);
		$countWord=count($arrRealWordList);
		if($countWord==0){
			#没查到 模糊查询
			$dictFileName=_FILE_DB_PALITEXT_;
			PDO_Connect("sqlite:$dictFileName");
			$query = "SELECT book,paragraph, text FROM pali_text WHERE text like ?  LIMIT ? , ?";
			$Fetch = PDO_FetchAll($query,array("%{$word}%",$_page*$_pagesize,$_pagesize));

			$result["data"]=$Fetch;
			exit;
		}
		$strQueryWordId="(";//实际出现的单词id查询字串
		$aQueryWordList=array();//id 为键 拼写为值的数组
		$aInputWordList=array();//id 为键 拼写为值的数组 该词是否被选择
		$aShowWordList=array();//拼写为键 个数为值的数组
		$aShowWordIdList=array();//拼写为键 值Id的数组
		for($i=0;$i<$countWord;$i++){
			$value= $arrRealWordList[$i];
			$strQueryWordId.="'{$value["id"]}',";
			$aQueryWordList["{$value["id"]}"]=$value["word"];
			$aInputWordList["{$value["id"]}"]=false;
			$aShowWordList[$value["word"]]=$value["count"];
			$aShowWordIdList[$value["word"]]=$value["id"];
		}

		if(isset($_GET["words"])){
			$word_selected = json_decode($_GET["words"]);
			if(count($word_selected)>0){
				$strQueryWordId="(";
				foreach ($word_selected as $key => $value) {
					$strQueryWordId.="'{$value}',";
					$aInputWordList["{$value}"] = true;
				}
			}
			else{
				foreach ($aInputWordList as $key => $value) {
					$aInputWordList[$key] = true;
				}
			}
		}
		else{
			foreach ($aInputWordList as $key => $value) {
				$aInputWordList[$key] = true;
			}
		}
		$strQueryWordId=mb_substr($strQueryWordId, 0,mb_strlen($strQueryWordId,"UTF-8")-1,"UTF-8");
		$strQueryWordId.=")";

		$queryTime=(microtime_float()-$time_start)*1000;
		
		//显示单词列表
		arsort($aShowWordList);
		
		$out_case = array();
		$word_count=0;
		foreach($aShowWordList as $x=>$x_value) {
			$caseword = array();
			$caseword["id"]=$aShowWordIdList[$x];
			$caseword["spell"] = $x;
			$caseword["count"] = $x_value;
			$caseword["selected"] = $aInputWordList["{$aShowWordIdList[$x]}"];
			$word_count+=$x_value;
			$out_case[] = $caseword;
		}
		$result["case"]=$out_case;
		$result["case_num"]=$countWord;
		$result["case_count"]=$word_count;
		
		//查找这些词出现在哪些书中

		$booklist=get_new_book_list($strQueryWordId);

		$result["book_list"]=$booklist;
		$result["book_tag"]=get_book_tag($strQueryWordId);
		
		$wordInBookCounter=0;
		$strFirstBookList="(";
		foreach($booklist as $onebook){
			$wordInBookCounter+=$onebook["count"];
			$strFirstBookList.="'".$onebook["book"]."',";
			if($wordInBookCounter>=20){
				break;
			}
		}
		$strFirstBookList=mb_substr($strFirstBookList, 0,mb_strlen($strFirstBookList,"UTF-8")-1,"UTF-8");
		$strFirstBookList.=")";	
		
		$strQueryBookId=" ";
		if(isset($_GET["book"])){
			$book_selected = json_decode($_GET["book"]);
			if(count($book_selected)>0){
				$strQueryBookId=" AND book IN (";
				foreach ($book_selected as $key => $value) {
					$strQueryBookId.="'{$value}',";
				}
				$strQueryBookId=mb_substr($strQueryBookId, 0,mb_strlen($strQueryBookId,"UTF-8")-1,"UTF-8");
				$strQueryBookId.=")";
			}
		}

		//前20条记录
		$time_start=microtime_float();
		$dictFileName=_FILE_DB_PALI_INDEX_;
		PDO_Connect("sqlite:$dictFileName");
		$query = "SELECT count(*) from (SELECT book FROM word WHERE \"wordindex\" in $strQueryWordId  $strQueryBookId group by book,paragraph) where 1 ";
		$result["record_count"]=  PDO_FetchOne($query);
		$query = "SELECT book,paragraph, wordindex, sum(weight) as wt FROM word WHERE \"wordindex\" in $strQueryWordId $strQueryBookId GROUP BY book,paragraph ORDER BY wt DESC LIMIT 0,20";
		$Fetch = PDO_FetchAll($query);
		
		$out_data = array();

		$queryTime=(microtime_float()-$time_start)*1000;
		$iFetch=count($Fetch);
		if($iFetch>0){
			$dictFileName=_FILE_DB_PALITEXT_;
			PDO_Connect("sqlite:$dictFileName");
			for($i=0;$i<$iFetch;$i++){
				$newRecode = array();

				$paliwordid=$Fetch[$i]["wordindex"];
				$paliword=$aQueryWordList["{$paliwordid}"];
				$book=$Fetch[$i]["book"];
				$paragraph=$Fetch[$i]["paragraph"];		
				$bookInfo = _get_book_info($book);
				$bookname=$bookInfo->title;
				$c1=$bookInfo->c1;
				$c2=$bookInfo->c2;
				$c3=$bookInfo->c3;

				//echo "<div class='dict_word' style='margin: 10px 0;padding: 5px;border-bottom: 1px solid var(--border-line-color);'>";
				//echo  "<div style='font-size: 130%;font-weight: 700;'>$paliword</div>";
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
					$newRecode["title"]=$sFirstParentTitle;
					$newRecode["path"] = $path;
					$newRecode["book"] = $book;
					$newRecode["para"] = $paragraph;
					$newRecode["palitext"] = $FetchPaliText[0]["html"];
					$newRecode["keyword"] = $paliword;
					$newRecode["wt"] = $Fetch[$i]["wt"];
//					echo  "<div class='mean' style='font-size:120%'><a href='../reader/?view=para&book={$book}&para={$paragraph}' target='_blank'>$path</a></div>";
					
					$out_data[] = $newRecode;
				}
				
			}
		}
		$queryTime=(microtime_float()-$time_start)*1000;
		$result["data"] = $out_data;
		echo json_encode($result,JSON_UNESCAPED_UNICODE);
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
								echo  "<div class='mean' style='font-size:120%;'><a href='../reader/?view=para&book={$book}&para={$paragraph}' target='_blank' >$path</a></div>";
																
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