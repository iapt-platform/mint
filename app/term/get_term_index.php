<?php
/*
查询term字典
输入单词列表
输出查到的结果
*/
require_once "../path.php";
require_once "../public/_pdo.php";

$output["status"]=0;
$output["error"]="";

PDO_Connect("sqlite:"._FILE_DB_TERM_);

if(isset($_POST["lang"])){
    $lang = $_POST["lang"];
}
else{
    $output["status"]=1;
    $output["error"]="#no_param lang";
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}

if(isset($_POST["word"])){
    $word = $_POST["word"];
    $query ="select word,meaning,language,owner,count(*) as co from term where word=? and language=? group by word,meaning order by co DESC";
    $output["data"] = PDO_FetchAll($query,array($word,$lang));
}
else{
    $query ="select * from (select word,meaning,language,owner,count(*) as co from term where language=? group by word,meaning order by co DESC) where 1 group by word";
    $output["data"] = PDO_FetchAll($query,array($lang));
    $pos = mb_strpos($lang,"-",0,"UTF-8");
    if($pos){
        $lang_family= mb_substr($lang,0,$pos,"UTF-8");
        $query ="select * from (select word,meaning,language,owner,count(*) as co from term where language like ? group by word,meaning order by co DESC) where 1 group by word";
        $otherlang = PDO_FetchAll($query,array($lang_family."%"));
        foreach ($otherlang as $key => $value) {
            $output["data"][]=$value;
        }
    }
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>