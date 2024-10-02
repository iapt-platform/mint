<?php
//工程文件操作
//建立，
include __DIR__."/../log/pref_log.php";
require_once __DIR__.'/../config.php';
require_once __DIR__."/../public/_pdo.php";
require_once __DIR__."/../public/function.php";
require_once __DIR__."/../channal/function.php";
require_once __DIR__."/../redis/function.php";
require_once __DIR__."/../public/snowflakeid.php";

# 雪花id
$snowflake = new SnowFlakeId();

define("MAX_LETTER" ,20000);

$output["status"]=0;
$output["error"]="";
$output["book"]="";
$output["para"]="";
$output["channel"]="";

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
$output["book"]=$_book;
$output["para"]=$_para;
$output["channel"]=$_channel;

//判断单词数量 太大的不能加载
PDO_Connect(_FILE_DB_PALITEXT_);

/*  创建一个填充了和 _para 相同数量占位符的字符串 */
$place_holders = implode(',', array_fill(0, count($_para), '?'));

$query = "SELECT sum(lenght) FROM "._TABLE_PALI_TEXT_." WHERE   paragraph IN ($place_holders) AND book = ?";
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
//PDO_Connect(_FILE_DB_USER_WBW_);

//模板库
PDO_Connect(_FILE_DB_PALICANON_TEMPLET_);
$dbh_tpl = $PDO;
$dbh_tpl->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

#用户逐词译库
$db_wbw = _FILE_DB_USER_WBW_;
$dbh_wbw= new PDO($db_wbw, _DB_USERNAME_, _DB_PASSWORD_);
$dbh_wbw->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
$channelClass = new Channal(redis_connect());
$channelInfo = $channelClass->getChannal($_channel);

foreach ($_para as $key => $para) {
    # code...
    $query = "SELECT count(*) FROM "._TABLE_USER_WBW_BLOCK_." WHERE channel_uid = ? AND book_id= ? and paragraph = ? ";
	$stmt = $dbh_wbw->prepare($query);
	$stmt->execute(array($_channel,$_book,$para));
	$row = $stmt->fetch(PDO::FETCH_NUM);
    if ($row) {
        $FetchWBW = $row[0];
    } else {
        $FetchWBW =  0;
    }
    if($FetchWBW == 0){
        #建立
        //写入数据库
        // 开始一个事务，关闭自动提交
        #更新block库
        $block_id=UUID::v4();
        $trans_block_id = UUID::v4();
        $block_data = array
						(
							$snowflake->id(),
							$block_id,                 
							"",            
							$_channel,
							$_COOKIE["userid"],
							$_COOKIE["uid"],
							$_book,
							$para,
							"",
							$channelInfo["lang"],
							$channelInfo["status"],
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
        $query="INSERT INTO "._TABLE_USER_WBW_BLOCK_." 
											( 
												id,
												uid ,
                                                parent_id ,
                                                channel_uid ,
                                                creator_uid ,
                                                editor_id ,
                                                book_id ,
                                                paragraph ,
                                                style ,
                                                lang ,
                                                status ,
                                                create_time ,
                                                modify_time 
											)
                                            VALUES ( ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ? , ?  )";
        $stmt_wbw = $dbh_wbw->prepare($query);
        $stmt_wbw->execute($block_data);
        // 提交更改 
        $dbh_wbw->commit();
        if (!$stmt_wbw || ($stmt_wbw && $stmt_wbw->errorCode() != 0)) {
            $error = $dbh_wbw->errorInfo();
            $output["status"]=1;
            $output["error"]=$error[2];
            echo json_encode($output, JSON_UNESCAPED_UNICODE);
            exit;
        }

        #逐词解析库
        $query="SELECT * FROM "._TABLE_PALICANON_TEMPLET_." WHERE book = ? AND paragraph = ? ";
        
        $sth = $dbh_tpl->prepare($query);
        $sth->execute(array($_book,$para));
        
        $level=100;
        $title="";
		#TODO 查不到数据报错
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
            $wbw_data[] = array
			(
				$snowflake->id(),
				UUID::v4(),
				$block_id,
				$_book,
				$para,
				$result["wid"],
				$result["real"],
				$strXml,
				mTime(),
				mTime(),
				0,
				$_COOKIE["userid"],
				$_COOKIE["uid"]
			);
        }
                
            // 开始一个事务，关闭自动提交

            $dbh_wbw->beginTransaction();
            $query="INSERT INTO "._TABLE_USER_WBW_." 
									( 
										id,
										uid ,
										block_uid ,
										book_id ,
										paragraph ,
										wid ,
										word ,
										data ,
										create_time ,
										modify_time ,
										status ,
										creator_uid ,
										editor_id 

									) 
                                    VALUES (? , ? , ? , ? , ? , ? , ? , ? , ? , ?  , ?  , ?  , ? )";
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
                exit;
            }

    }
}

echo json_encode($output, JSON_UNESCAPED_UNICODE);
PrefLog();
?>