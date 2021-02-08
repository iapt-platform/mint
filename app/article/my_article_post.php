<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
require_once "../ucenter/active.php";

add_edit_event(_ARTICLE_EDIT_,$_POST["id"]);

$respond=array("status"=>0,"message"=>"");

# 检查是否由修改权限
PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);
$query = "SELECT owner FROM  article WHERE id= ?";
$owner = PDO_FetchOne($query,array($_POST["id"]));
if($owner!=$_COOKIE["userid"]){
    $respond["status"]=1;
    $respond["message"]="No Power For Edit";
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

$_content = $_POST["content"];

if($_POST["import"]=='on'){
    $sent = explode("\n",$_POST["content"]);
    if($sent && count($sent)>0){
        $setting =  new Hostsetting();
        $max_book = $setting->get("max_book_number");
        if($max_book){
            $currBook = $max_book+1;
            $setbooknum = $setting->set("max_book_number",$currBook);
            if($setbooknum==false){
                $respond["status"]=1;
                $respond["message"]="设置书号错误";
                echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                exit;
            }
        }
        else{
            $respond["status"]=1;
            $respond["message"]="获取书号错误";
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        }
        PDO_Connect("sqlite:"._FILE_DB_SENTENCE_);

        /* 开始一个事务，关闭自动提交 */
        $PDO->beginTransaction();
        $query="INSERT INTO sentence ('id','block_id','channal','book','paragraph','begin','end','tag','author','editor','text','language','ver','status','strlen','create_time','modify_time','receive_time') VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? , ?, ?)";
        
        $sth = $PDO->prepare($query);
        
        $para = 1;
        $sentNum = 0;
        $newText =  "";
        foreach ($sent as $data) {
            $data = trim($data);
            if($data==""){
                $para++;
                $sentNum = 0;
                $newText .="\n";
                continue;
            }
            else{
                $sentNum=$sentNum+10;
            }
            if(mb_substr($data,0,2,"UTF-8")=="{{"){
                $newText .=$data."\n";
            }
            else{
                $newText .='{{'."{$currBook}-{$para}-{$sentNum}-{$sentNum}"."}}\n";
                $sth->execute(
                        array(UUID::v4(),
                                    "",
                                    $_POST["channal"],
                                    $currBook,
                                    $para,
                                    $sentNum,
                                    $sentNum,
                                    "",
                                    "[]",
                                    $_COOKIE["userid"],
                                    $data,
                                    $_POST["lang"],
                                    1,
                                    1,
                                    mb_strlen($data,"UTF-8"),
                                    mTime(),
                                    mTime(),
                                    mTime()
                                ));                
            }

        }
        $PDO->commit();
        
        if (!$sth || ($sth && $sth->errorCode() != 0)) {
            /*  识别错误且回滚更改  */
            $PDO->rollBack();
            $error = PDO_ErrorInfo();
            $respond['status']=1;
            $respond['message']=$error[2];
            echo json_encode($respond, JSON_UNESCAPED_UNICODE);
            exit;
        }
        else{
            $respond['status']=0;
            $respond['message']="成功";
            $_content = $newText;
        }		        
    }
}

PDO_Connect("sqlite:"._FILE_DB_USER_ARTICLE_);

$query="UPDATE article SET title = ? , subtitle = ? , summary = ?, content = ?  , tag = ? , setting = ? , status = ? , receive_time= ?  , modify_time= ?   where  id = ?  ";
$sth = $PDO->prepare($query);
$sth->execute(array($_POST["title"] , $_POST["subtitle"] ,$_POST["summary"], $_content , $_POST["tag"] , $_POST["setting"] , $_POST["status"] ,   mTime() , mTime() , $_POST["id"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>