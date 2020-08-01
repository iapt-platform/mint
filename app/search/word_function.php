<?php
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/load_lang.php";//语言文件
require_once "../public/function.php";

function render_book_list($strWordlist,$booklist=null){
	//查找这些词出现在哪些书中
	$arrBookType=json_decode(file_get_contents("../public/book_name/booktype.json"));
	$dictFileName=_FILE_DB_BOOK_WORD_;
	PDO_Connect("sqlite:$dictFileName");	
	if(isset($booklist)){
		foreach($booklist as $oneBook){
			$aInputBook["{$oneBook}"]=1;
		}
	}
	$query = "select book,sum(count) as co from bookword where \"wordindex\" in $strWordlist group by book order by co DESC";
	$Fetch = PDO_FetchAll($query);
	$iFetch=count($Fetch);
	$newBookList=array();
	if($iFetch>0){
		$booktypesum["vinaya"]=array(0,0);
		$booktypesum["sutta"]=array(0,0);
		$booktypesum["abhidhamma"]=array(0,0);
		$booktypesum["anna"]=array(0,0);
		$booktypesum["mula"]=array(0,0);
		$booktypesum["atthakattha"]=array(0,0);
		$booktypesum["tika"]=array(0,0);
		$booktypesum["anna2"]=array(0,0);
		for($i=0;$i<$iFetch;$i++){
			$book=$Fetch[$i]["book"];
			$sum=$Fetch[$i]["co"];
			array_push($newBookList,array($book,$sum));
			$t1=$arrBookType[$book-1]->c1;
			$t2=$arrBookType[$book-1]->c2;
			if(isset($booktypesum[$t1])){
				$booktypesum[$t1][0]++;
				$booktypesum[$t1][1]+=$sum;
			}
			else{
				$booktypesum[$t1][0]=1;
				$booktypesum[$t1][1]=$sum;
			}
			if(isset($booktypesum[$t2])){
				$booktypesum[$t2][0]++;
				$booktypesum[$t2][1]+=$sum;
			}
			else{
				$booktypesum[$t2][0]=1;
				$booktypesum[$t2][1]=$sum;
			}
		}
		echo "<div id='bold_book_list_new'>";
		echo "出现在{$iFetch}本书中：<br />";
		echo "<input id='bold_all_book' type='checkbox' checked onclick=\"dict_bold_book_all_select()\" />全选<br />";
		echo "<input id='id_book_filter_vinaya' type='checkbox' checked onclick=\"search_book_filter('id_book_filter_vinaya','vinaya')\" />律藏-{$booktypesum["vinaya"][0]}-{$booktypesum["vinaya"][1]}<br />";
		echo "<input id='id_book_filter_sutta'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_sutta','sutta')\" />经藏-{$booktypesum["sutta"][0]}-{$booktypesum["sutta"][1]}<br />";
		echo "<input id='id_book_filter_abhidhamma'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_abhidhamma','abhidhamma')\" />阿毗达摩藏-{$booktypesum["abhidhamma"][0]}-{$booktypesum["abhidhamma"][1]}<br />";
		echo "<input id='id_book_filter_anna'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_anna','anna')\" />其他-{$booktypesum["anna"][0]}-{$booktypesum["anna"][1]}<br /><br />";	
		echo "<input id='id_book_filter_mula' type='checkbox' checked onclick=\"search_book_filter('id_book_filter_mula','mula')\" />根本-{$booktypesum["mula"][0]}-{$booktypesum["mula"][1]}<br />";
		echo "<input id='id_book_filter_atthakattha'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_atthakattha','atthakattha')\" />义注-{$booktypesum["atthakattha"][0]}-{$booktypesum["atthakattha"][1]}<br />";
		echo "<input id='id_book_filter_tika'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_tika','tika')\" />复注-{$booktypesum["tika"][0]}-{$booktypesum["tika"][1]}<br />";
		echo "<input id='id_book_filter_anna2'  type='checkbox' checked onclick=\"search_book_filter('id_book_filter_anna2','anna2')\" />其他-{$booktypesum["anna2"][0]}-{$booktypesum["anna2"][1]}<br /><br />";
		for($i=0;$i<$iFetch;$i++){

			$book=$Fetch[$i]["book"];
			$bookname=_get_book_info($book)->title;	
			if(isset($booklist)){
				if(isset($aInputBook["{$book}"])){
					$bookcheck="checked";
				}
				else{
					$bookcheck="";
				}
			}
			else{
				$bookcheck="checked";
			}
			$t1=$arrBookType[$book-1]->c1;
			$t2=$arrBookType[$book-1]->c2;
			echo "<div class='{$t1}'>";
			echo "<div class='{$t2}'>";
			echo "<input id='bold_book_{$i}' type='checkbox' $bookcheck value='{$book}'/>";
			echo "<a onclick=\"dict_bold_book_select({$i})\">";
			echo "《{$bookname}》({$Fetch[$i]["co"]})<br />";
			echo "</a>";
			echo "</div></div>";
		}
		echo "<input id='bold_book_count' type='hidden' value='{$iFetch}' />";
		echo "</div>";
	}
	
	return($newBookList);
	//查找这些词出现在哪些书中结束
				
}

function countWordInPali($word,$sort=false){
            //加语尾
            $case =  $GLOBALS['case'];
            $union = $GLOBALS['union'];
		$arrNewWord=array();
		for ($row = 0; $row < count($case); $row++) {
			$len=mb_strlen($case[$row][0],"UTF-8");
			$end=mb_substr($word, 0-$len,NULL,"UTF-8");
			if($end==$case[$row][0]){
				$newWord=mb_substr($word, 0,mb_strlen($word,"UTF-8")-$len,"UTF-8").$case[$row][1];
				$arrNewWord[$newWord]=1;
			}
		}
		//加连读词尾
		$arrUnWord=array();
		for ($row = 0; $row < count($union); $row++) {
			$len=mb_strlen($union[$row][0],"UTF-8");
			foreach($arrNewWord as $x=>$x_value){
				$end=mb_substr($x, 0-$len,NULL,"UTF-8");
				if($end==$union[$row][0]){
					$newWord=mb_substr($x, 0,mb_strlen($x,"UTF-8")-$len,"UTF-8").$union[$row][1];
					$arrUnWord[$newWord]=1;
				}
			}
		}
		//将连读词和$arrNewWord混合
		foreach($arrUnWord as $x=>$x_value){
			$arrNewWord[$x]=1;
		}
		if(count($arrNewWord)>0){
			$strQueryWord="(";
			foreach($arrNewWord as $x=>$x_value) {
			  $strQueryWord.="'{$x}',";
			}
			$strQueryWord=mb_substr($strQueryWord, 0,mb_strlen($strQueryWord,"UTF-8")-1,"UTF-8");
			$strQueryWord.=")";
		}
		else{
			$strQueryWord="('{$word}')";
		}

		//查找实际出现的拼写

        $dsn = "sqlite:"._FILE_DB_word_INDEX_;
        $user = "";
        $password = "";
        $PDO = new PDO($dsn, $user, $password,array(PDO::ATTR_PERSISTENT=>true));
        $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        if($sort){
            $query = "select id,word,count,bold,len from wordindex where \"word\" in  $strQueryWord order by count DESC";
        }
        else{
            $query = "select id,word,count,bold,len from wordindex where \"word\" in  $strQueryWord";
        }
        
        $stmt = $PDO->query($query);
        $arrRealWordList = $stmt->fetchAll(PDO::FETCH_ASSOC);


        return($arrRealWordList);

}
?>