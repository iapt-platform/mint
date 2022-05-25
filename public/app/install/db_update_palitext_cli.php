<?php
/*
生成 巴利原文段落表
 */
require_once __DIR__."/../config.php";
require_once __DIR__."/../public/_pdo.php";

set_exception_handler(function($e){
	fwrite(STDERR,"error-msg:".$e->getMessage().PHP_EOL);
	fwrite(STDERR,"error-file:".$e->getFile().PHP_EOL);
	fwrite(STDERR,"error-line:".$e->getLine().PHP_EOL);
	exit;
});

define("_DB_PALITEXT_", _PG_DB_PALITEXT_);
define("_TABLE_",_PG_TABLE_PALI_TEXT_);

fwrite(STDOUT, "Update Pali Text ".PHP_EOL);

if ($argc != 3) {
	echo "help".PHP_EOL;
	echo $argv[0]." from to".PHP_EOL;
	echo "from = 1-217".PHP_EOL;
	echo "to = 1-217".PHP_EOL;
	exit;
}
$_from = (int) $argv[1];
$_to = (int) $argv[2];
if ($_to > 217) {
	$_to = 217;
}


$to = $_to;

$filelist = array();
$fileNums = 0;
$log = "";

if (($handle = fopen(__DIR__."/filelist.csv", 'r')) !== false) {
    while (($filelist[$fileNums] = fgetcsv($handle, 0, ',')) !== false) {
        $fileNums++;
    }
}
if ($to == 0 || $to > $fileNums) {
    $to = $fileNums;
}



PDO_Connect(_DB_PALITEXT_,_DB_USERNAME_,_DB_PASSWORD_);


for ($from=$_from-1; $from < $to; $from++) { 
    fwrite(STDOUT, "doing $_from".PHP_EOL);

    $FileName = $filelist[$from][1] . ".htm";
    $fileId = $filelist[$from][0];
    $fileId = $filelist[$from][0];

    $dirLog = _DIR_LOG_ . "/";

    $dirDb = "/";
    $inputFileName = $FileName;
    $outputFileNameHead = $filelist[$from][1];
    $bookId = $filelist[$from][2];
    $vriParNum = 0;
    $wordOrder = 1;

    $dirXmlBase = _DIR_PALI_CSV_ . "/";
    $dirPaliTextBase = _DIR_PALI_HTML_ . "/";
    $dirXml = $outputFileNameHead . "/";

    $xmlfile = $inputFileName;
    fwrite(STDOUT, "doing:" . $xmlfile . PHP_EOL);

    $log = $log . date("Y-m-d h:i:sa") . ",$from,$FileName,open\r\n";

    $arrInserString = array();

    // 打开vri html文件并读取数据
    $pali_text_array = array(); //vri text
    if (($fpPaliText = fopen($dirPaliTextBase . $xmlfile, "r")) !== false) {
        while (($data = fgets($fpPaliText)) !== false) {
            array_push($pali_text_array, $data);
        }
        fclose($fpPaliText);
        fwrite(STDOUT, "pali text load：" . $dirPaliTextBase . $xmlfile . PHP_EOL);
    } else {
        fwrite(STDERR, "can not pali text file. filename=" . $dirPaliTextBase . $xmlfile.PHP_EOL);
    }

    // 打开csv文件并读取数据
    $inputRow = 0;
    $tocFile = _DIR_PALI_TITLE_ . "/" . ($from + 1) . "_pali.csv";
    if (($fp = fopen($tocFile, "r")) !== false) {
        while (($data = fgetcsv($fp, 0, ',')) !== false) {
            if ($inputRow > 0) {
                $params = $data;
                array_push($arrInserString, $params);
            }
            $inputRow++;
        }
        fclose($fp);
        fwrite(STDOUT, "Toc load：" . $tocFile. PHP_EOL);
    } else {
        fwrite(STDERR, "can not open csv file. filename=" . $tocFile . PHP_EOL);
    }

    if ((count($arrInserString)) != count($pali_text_array) - 2) {
        $log = $log . "$from, $FileName,error,文件行数不匹配 csv = " . (count($arrInserString) - 1) . " pali_text_array=" . (count($pali_text_array) - 2) . " \r\n";
    }

    $book = $from + 1;

    //计算段落信息，如上一段

    $query = "SELECT book,paragraph,level,toc,lenght from "._TABLE_." where book = '$book'  order by paragraph asc";
    $title_data = PDO_FetchAll($query);
    fwrite(STDOUT, "Paragraph Count:" . count($title_data) . " arrInserString:".count($arrInserString). PHP_EOL);

    $paragraph_count = count($title_data);

    // 开始一个事务，关闭自动提交
    $PDO->beginTransaction();
    $query = "UPDATE "._TABLE_." SET level = ? , toc = ? , chapter_len = ? , next_chapter = ?, prev_chapter=? , parent= ?  ,  chapter_strlen = ? , path = ? ,updated_at = now()  WHERE book=? and paragraph=?";
    $stmt = $PDO->prepare($query);

    $paragraph_info = array();
    array_push($paragraph_info, array($from, -1, $paragraph_count, -1, -1, -1));


    for ($iPar = 0; $iPar < count($title_data); $iPar++) {
        $title_data[$iPar]["level"] = $arrInserString[$iPar][3];
    }


    for ($iPar = 0; $iPar < count($title_data); $iPar++) {
        $book = $from + 1;
        $paragraph = $title_data[$iPar]["paragraph"];

    /*
    level 8 为 偈诵编号。不当作标题
    */
    if ((int)$title_data[$iPar]["level"] == 8) {
        $title_data[$iPar]["level"] = 100;
    }

    $curr_level = (int) $title_data[$iPar]["level"];
    # 计算这个chapter的段落数量
    $length = -1;
   
    
    for ($iPar1 = $iPar + 1; $iPar1 < count($title_data); $iPar1++) {
        $thislevel = (int) $title_data[$iPar1]["level"];
        if ($thislevel <= $curr_level) {
            $length = (int) $title_data[$iPar1]["paragraph"] - $paragraph;
            break;
        }
    }

    if ($length == -1) {
        $length = $paragraph_count - $paragraph + 1;
    }

    
    /*
    上一个段落
    算法：查找上一个标题段落。而且该标题段落的下一个段落不是标题段落
    */
    $prev = -1;
    if ($iPar > 0) {
        for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
            if ($title_data[$iPar1]["level"] < 8 && $title_data[$iPar1+1]["level"]==100) {
                $prev = $title_data[$iPar1]["paragraph"];
                break;
            }
        }
    }

    /*
    上一个段落
    算法：查找上一个标题段落。而且该标题段落的下一个段落不是标题段落
    */
    $next = -1;
    if ($iPar < count($title_data) - 1) {
        for ($iPar1 = $iPar + 1; $iPar1 < count($title_data)-1; $iPar1++) {
            if ($title_data[$iPar1]["level"] <8 && $title_data[$iPar1+1]["level"]==100) {
                $next = $title_data[$iPar1]["paragraph"];
                break;
            }
        }
    }

    $parent = -1;
    if ($iPar > 0) {
        for ($iPar1 = $iPar - 1; $iPar1 >= 0; $iPar1--) {
            if ($title_data[$iPar1]["level"] < $curr_level) {
                $parent = $title_data[$iPar1]["paragraph"];
                break;
            }
        }
    }
    //计算章节包含总字符数
    $iChapter_strlen = 0;

    for ($i = $iPar; $i < $iPar + $length; $i++) {
        $iChapter_strlen += $title_data[$i]["lenght"];
    }
    

        $title_data[$iPar]["level"] = $arrInserString[$iPar][3];
        $title_data[$iPar]["toc"] = $arrInserString[$iPar][5];
        $title_data[$iPar]["parent"] = $parent;
        /*
        *获取路径
        */
        $currParent = $parent;
        $path = [];
        $iLoop = 0;
        while ($currParent != -1 && $iLoop<7) {
            # code...
            $pathTitle = $title_data[$currParent-1]["toc"];
            $pathLevel = $title_data[$currParent-1]['level'];
            $path[] = ["book"=>$book,"paragraph"=>$currParent,"title"=>$pathTitle,"level"=>$pathLevel];
            $currParent = $title_data[$currParent-1]["parent"];
            $iLoop++;
        }
        # 将路径反向
        $path1 = [];
        for ($i=count($path)-1; $i >=0 ; $i--) { 
            # code...
            $path1[] = $path[$i];
        }

    $newData = array(
        $arrInserString[$iPar][3],
        $arrInserString[$iPar][5],
        $length,
        $next,
        $prev,
        $parent,
        $iChapter_strlen,
        json_encode($path1),
        $book,
        $paragraph,
    );

    $stmt->execute($newData);

    if ($curr_level > 0 && $curr_level < 8) {
        array_push($paragraph_info, array($book, $paragraph, $length, $prev, $next, $parent));
    }
}

// 提交更改
$PDO->commit();
if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    $error = PDO_ErrorInfo();
    fwrite(STDERR, "error - $error[2]". PHP_EOL);

    $log = $log . "$from, $FileName, error, $error[2] \r\n";
} else {
    $count = count($title_data);
    fwrite(STDOUT, "updata $count paragraph info recorders.". PHP_EOL);
    fwrite(STDOUT, count($paragraph_info) . " Heading". PHP_EOL);
}
//段落信息结束

}
fwrite(STDOUT, "all done!".PHP_EOL);
?>

