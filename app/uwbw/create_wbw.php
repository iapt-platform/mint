<?php
//工程文件操作
//建立，
require_once '../path.php';
require_once "../public/_pdo.php";
require_once "../public/function.php";
define("MAX_LETTER" ,20000);

$output["status"]=0;
$output["error"]="";
$output{"book"}="";
$output{"para"}="";
$output{"channel"}="";

if(isset($_POST["book"])){
    $_book = $_POST["book"];
}
else{
    $output["status"]=1;
    $output["error"]="#no_param book";
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}
if(isset($_POST["para"])){
    $_para = json_decode($_POST["para"]);
}
else{
    $output["status"]=1;
    $output["error"]="#no_param paragraph";
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}
if(isset($_POST["channel"])){
    $_channel = $_POST["channel"];
}
else{
    $output["status"]=1;
    $output["error"]="#no_param channel";
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}
$output{"book"}=$_book;
$output{"para"}=$_para;
$output{"channel"}=$_channel;

//判断单词数量 太大的不能加载
PDO_Connect(""._FILE_DB_PALITEXT_);
$params = array(1, 21, 63, 171);
/*  创建一个填充了和params相同数量占位符的字符串 */
$place_holders = implode(',', array_fill(0, count($_para), '?'));

$query = "SELECT sum(lenght) FROM pali_text WHERE   paragraph IN ($place_holders) AND book = ?";
$param_letter = $_para;
$param_letter[] = $_book;
$sum_len = PDO_FetchOne($query,$param_letter);

if($sum_len>MAX_LETTER){
    $output["status"]=1;
    $output["error"]="#oversize_to_load";
    echo json_encode($output, JSON_UNESCAPED_UNICODE);
    exit;
}


# 查询数据库是否有数据，没有就建立
// 查询逐词解析库
PDO_Connect(""._FILE_DB_USER_WBW_);

//模板库
$db_tpl = ""._DIR_PALICANON_TEMPLET_."/p".$_book."_tpl.db3";
$dbh_tpl = new PDO($db_tpl, "", "");
$dbh_tpl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

#用户逐词译库
$db_wbw = ""._FILE_DB_USER_WBW_;
$dbh_wbw= new PDO($db_wbw, "", "");
$dbh_wbw->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

foreach ($_para as $key => $para) {
    # code...
    $query = "SELECT count(*) FROM wbw_block WHERE channal = ? AND book= ? and paragraph = ? ";
    $FetchWBW = PDO_FetchOne($query,array($_channel,$_book,$para));
    if($FetchWBW==0){
        #建立
        //写入数据库
        // 开始一个事务，关闭自动提交
        #更新block库
        $block_id=UUID::v4();
        $trans_block_id = UUID::v4();
        $block_data = array($block_id,
                                         "",
                                         $_channel,
                                         $_COOKIE["userid"],
                                         $_book,
                                         $para,
                                         "",
                                         $_POST["lang"],
                                         1,
                                         mTime(),
                                         mTime()
                                        );
        $block_list[] = array("channal"=>$_channel,
                                        "type"=>6,//word by word
                                        "book"=>$_book,
                                        "paragraph"=>$para,
                                        "block_id"=>$block_id,
                                        "readonly"=>false
                                    );
        $dbh_wbw->beginTransaction();
        $query="INSERT INTO wbw_block ('id',
                                                                 'parent_id',
                                                                 'channal',
                                                                 'owner',
                                                                 'book',
                                                                 'paragraph',
                                                                 'style',
                                                                 'lang',
                                                                 'status',
                                                                 'modify_time',
                                                                 'receive_time')
                                                  VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )";
        $stmt_wbw = $dbh_wbw->prepare($query);
        $stmt_wbw->execute($block_data);
        // 提交更改 
        $dbh_wbw->commit();
        if (!$stmt_wbw || ($stmt_wbw && $stmt_wbw->errorCode() != 0)) {
            $error = $dbh_wbw->errorInfo();
            $output["status"]=1;
            $output["error"]=$error[2];
            echo json_encode($output, JSON_UNESCAPED_UNICODE);
            eixt;
        }

        #逐词解析库
        $query="SELECT * FROM 'main' WHERE paragraph = ? ";
        
        $sth = $dbh_tpl->prepare($query);
        $sth->execute(array($para));
        
        $level=100;
        $title="";
        while($result = $sth->fetch(PDO::FETCH_ASSOC))
        {
            if($result["gramma"]=="?"){
                $wGrammar="";
            }
            else{
                $wGrammar=$result["gramma"];
            }

            $strXml="<word>";
            $strXml.="<pali>{$result["word"]}</pali>";
            $strXml.="<real>{$result["real"]}</real>";
            $wordid = "p{$result["book"]}-{$result["paragraph"]}-{$result["wid"]}"; 
            $strXml.="<id>{$wordid}</id>";
            $strXml.="<type s=\"0\">{$result["type"]}</type>";
            $strXml.="<gramma s=\"0\">{$wGrammar}</gramma>";
            $strXml.="<mean s=\"0\"></mean>";
            $strXml.="<org s=\"0\">".mb_strtolower($result["part"], 'UTF-8')."</org>";
            $strXml.="<om s=\"0\"></om>";
            $strXml.="<case s=\"0\">{$result["type"]}#{$wGrammar}</case>";
            $strXml.="<style>{$result["style"]}</style>";
            $strXml.="<status>0</status>";
            $strXml.="</word>";
            $wbw_data[] = array(UUID::v4(),
                                              $block_id,
                                              $_book,
                                              $para,
                                              $result["wid"],
                                              $result["real"],
                                              $strXml,
                                              mTime(),
                                              mTime(),
                                              1,
                                              $_COOKIE["userid"]
                                            );
        }
                
            // 开始一个事务，关闭自动提交

            $dbh_wbw->beginTransaction();
            $query="INSERT INTO wbw ('id',
                                                           'block_id',
                                                           'book',
                                                           'paragraph',
                                                           'wid',
                                                           'word',
                                                           'data',
                                                           'modify_time',
                                                           'receive_time',
                                                           'status',
                                                           'owner'
                                                           ) 
                                           VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? )";
            $stmt_wbw = $dbh_wbw->prepare($query);
            foreach($wbw_data as $oneParam){
                $stmt_wbw->execute($oneParam);
            }
            // 提交更改 
            $dbh_wbw->commit();
            if (!$stmt_wbw || ($stmt_wbw && $stmt_wbw->errorCode() != 0)) {
                $error = $dbh_wbw->errorInfo();
                $output["status"]=1;
                $output["error"]=$error[2];
                echo json_encode($output, JSON_UNESCAPED_UNICODE);
                eixt;
            }

    }
}

/*TO DO 
            //更新服务器端文件列表
            $db_file = _FILE_DB_FILEINDEX_;
            PDO_Connect("$db_file");
            $query="INSERT INTO fileindex ('id',
                                        'parent_id',
                                        'channal',
                                        'user_id',
                                        'book',
                                        'paragraph',
                                        'file_name',
                                        'title',
                                        'tag',
                                        'status',
                                        'create_time',
                                        'modify_time',
                                        'accese_time',
                                        'file_size',
                                        'share',
                                        'doc_info',
                                        'doc_block',
                                        'receive_time'
                                        ) 
                            VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)";
            $stmt = $PDO->prepare($query);
            $doc_id=UUID::v4();
            $file_name = $book . '_' . $create_para . '_' . time();
            $newData=array(
                           $doc_id,
                           "",
                           $_POST["channal"],
                           $uid,
                           $book,
                           $create_para,
                           $file_name,
                           $user_title,
                           $tag,
                           1,
                           mTime(),
                           mTime(),
                           mTime(),
                           $filesize,
                           0,
                           $doc_head,
                           json_encode($block_list, JSON_UNESCAPED_UNICODE),
                           mTime()
                           );
            $stmt->execute($newData);
            if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                $error = PDO_ErrorInfo();
                echo "error - $error[2] <br>";
            }
            else{
                echo "成功新建一个文件.";
            }

*/
echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>