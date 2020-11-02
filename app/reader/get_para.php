<?php
require_once "../public/_pdo.php";
require_once "../path.php";

define("_MAX_CHAPTER_LEN_" , 20000);

$_book = $_GET["book"];
$_para = $_GET["para"];
if(isset($_GET["begin"])){
    $_begin = $_GET["begin"];
}
if(isset($_GET["end"])){
    $_end = $_GET["end"];
}
$_view = $_GET["view"];

$output["toc"] = array();
$output["sentences"] = array();
$output["head"] = 0;

if($_view=="sent"){
    $output["sentences"] = array(array("book"=>$_book,"paragraph"=>$_para,"begin"=>$_begin,"end"=>$_end));
    echo json_encode($output,JSON_UNESCAPED_UNICODE);
    exit;
}



PDO_Connect("sqlite:"._FILE_DB_PALITEXT_);
$query = "SELECT * FROM 'pali_text'  WHERE book= ? AND paragraph= ?";
$FetchParInfo = PDO_FetchRow($query , array($_book,$_para));
if($FetchParInfo){
    switch ($_view) {
        case 'chapter':
            # code...
            $paraBegin = $_para;
			$paraEnd = $_para+$FetchParInfo["chapter_len"]-1;
            break;
        case 'para':
            $paraBegin = $_para;
			$paraEnd = $_para;
            # code...
            break;        
        default:
            # code...
            $paraBegin = $_para;
			$paraEnd = $_para;
            break;
    }

    //获取下级目录
    $query = "SELECT * FROM 'pali_text'  WHERE book= ? AND (paragraph BETWEEN ?AND ? ) AND level < 100 ";
    $output["toc"] = PDO_FetchAll($query , array($_book,$paraBegin,$paraEnd));

    if($FetchParInfo["chapter_strlen"]>_MAX_CHAPTER_LEN_ && $_view === "chapter" && count($output["toc"])>1){
        if($output["toc"][1]["paragraph"]-$_para>1){
            # 中间有间隔
            $paraBegin = $_para;
            $paraEnd = $output["toc"][1]["paragraph"]-1;
            $output["head"] = 1;
        }
        else{
            #中间无间隔
            echo json_encode($output,JSON_UNESCAPED_UNICODE);
            exit;            
        }

    }

    PDO_Connect("sqlite:"._FILE_DB_PALI_SENTENCE_);

    $query = "SELECT book,paragraph,begin, end FROM 'pali_sent' WHERE book= ? AND (paragraph BETWEEN ?AND ? ) ";
    $sent_list = PDO_FetchAll($query,array($_book,$paraBegin,$paraEnd));
    $output["sentences"] = $sent_list;
    echo json_encode($output,JSON_UNESCAPED_UNICODE);        

}
else{
    echo json_encode($output,JSON_UNESCAPED_UNICODE);

}
?>