<?php
//全文搜索
require_once '../path.php';
require_once '../public/casesuf.inc';
require_once '../public/union.inc';
require_once "../public/_pdo.php";
require_once "../public/load_lang.php";//语言文件
require_once "../public/function.php";
require_once "../search/word_function.php";

    $resulte = array();
    $resulte[] = array("id"=>"0.0", 'parent' => '',  'name' => '全部'  );

    $resulte[] = array("id"=>"sutta", 'parent' => '0.0',  'name' => '经藏'  );
    $resulte[] = array("id"=>"vinaya", 'parent' => '0.0',  'name' => '律藏'  );
    $resulte[] = array("id"=>"abhidhamma", 'parent' => '0.0',  'name' => '论藏'  );
    $resulte[] = array("id"=>"anna", 'parent' => '0.0',  'name' => '其他'  );

    $resulte[] = array("id"=>"sutta.mula", 'parent' => 'sutta',  'name' => '根本'  );
    $resulte[] = array("id"=>"sutta.atthakattha", 'parent' => 'sutta',  'name' => '义注'  );
    $resulte[] = array("id"=>"sutta.tika", 'parent' => 'sutta',  'name' => '复注'  );
    $resulte[] = array("id"=>"sutta.anna", 'parent' => 'sutta',  'name' => '其他'  );

    $resulte[] = array("id"=>"vinaya.mula", 'parent' => 'vinaya',  'name' => '根本'  );
    $resulte[] = array("id"=>"vinaya.atthakattha", 'parent' => 'vinaya',  'name' => '义注'  );
    $resulte[] = array("id"=>"vinaya.tika", 'parent' => 'vinaya',  'name' => '复注'  );
    $resulte[] = array("id"=>"vinaya.anna", 'parent' => 'vinaya',  'name' => '其他'  );

    $resulte[] = array("id"=>"abhidhamma.mula", 'parent' => 'abhidhamma',  'name' => '根本'  );
    $resulte[] = array("id"=>"abhidhamma.atthakattha", 'parent' => 'abhidhamma',  'name' => '义注'  );
    $resulte[] = array("id"=>"abhidhamma.tika", 'parent' => 'abhidhamma',  'name' => '复注'  );
    $resulte[] = array("id"=>"abhidhamma.anna", 'parent' => 'abhidhamma',  'name' => '其他'  );

    $resulte[] = array("id"=>"anna.atthakattha", 'parent' => 'anna',  'name' => '义注'  );
    $resulte[] = array("id"=>"anna.tika", 'parent' => 'anna',  'name' => '复注'  );
    $resulte[] = array("id"=>"anna.anna2", 'parent' => 'anna',  'name' => '其他'  );

    if(isset($_GET["word"])){
        $word=mb_strtolower($_GET["word"],'UTF-8'); 
    }
    else{
        echo json_encode($resulte,JSON_UNESCAPED_UNICODE);
        exit;
    }
    _load_book_index();

    $count_return=0;
    $dict_list=array();



	//计算某词在三藏中出现的次数		
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
    
            
    
    //显示单词列表

    arsort($aShowWordList);
    $i=0;
    foreach($aShowWordList as $x=>$x_value) {
        $wordid=$aShowWordIdList[$x];
        //echo $x.":".$x_value."<br />";
        $i++;
    }

    	//查找这些词出现在哪些书中
	$arrBookType=json_decode(file_get_contents("../public/book_name/booktype.json"));
	$dictFileName=_FILE_DB_BOOK_WORD_;
	PDO_Connect("sqlite:$dictFileName");	
	if(isset($booklist)){
		foreach($booklist as $oneBook){
			$aInputBook["{$oneBook}"]=1;
		}
	}
	$query = "select book,sum(count) as co from bookword where \"wordindex\" in $strQueryWordId group by book order by co DESC";
	$Fetch = PDO_FetchAll($query);
	$iFetch=count($Fetch);
	$newBookList=array();
	if($iFetch>0){
		for($i=0;$i<$iFetch;$i++){
			$book=$Fetch[$i]["book"];
            $sum=$Fetch[$i]["co"];
            $sum = $sum + 1;
            $sum--;
			array_push($newBookList,array($book,$sum));
			$t1=$arrBookType[$book-1]->c1;
            $t2=$arrBookType[$book-1]->c2;
            $resulte[] = array("id"=>"book".$book, 'parent' => $t1.'.'.$t2,  'name' => _get_book_info($book)->title, 'value' => $sum  );
        }
    }

    echo json_encode($resulte,JSON_UNESCAPED_UNICODE);

?>