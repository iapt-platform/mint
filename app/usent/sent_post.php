<?php
#更新一个句子
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";
require_once "../usent/function.php";
require_once "../ucenter/active.php";

#检查是否登陆
if(!isset($_COOKIE["userid"])){
    $respond["status"] = 1;
    $respond["message"] = 'not login';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}
if(isset($_POST["landmark"])){
    $_landmark = $_POST["landmark"];
}
else{
    $_landmark = "";
}
//回传数据
$respond=array("status"=>0,"message"=>"");
$respond['book']=$_POST["book"];
$respond['para']=$_POST["para"];
$respond['begin']=$_POST["begin"];
$respond['end']=$_POST["end"];
$respond['channal']=$_POST["channal"];
$respond['text']=$_POST["text"];
$respond['editor']=$_COOKIE["userid"];

#先查询对此channal是否有权限修改
$cooperation = 0;
$text_lang = "en";
$channel_status = 0;
if(isset($_POST["channal"])){
   PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
    $query = "SELECT owner, lang , status FROM channal WHERE id=?";
    $fetch = PDO_FetchRow($query,array($_POST["channal"]));
    
    if($fetch){
        $text_lang = $fetch["lang"];
        $channel_status = $fetch["status"];
    }
    $respond['lang']=$text_lang;
    if($fetch && $fetch["owner"]==$_COOKIE["userid"]){
        #自己的channal
        $cooperation = 1;
    }
    else{
        $query = "SELECT count(*) FROM cooperation WHERE channal_id= ? and user_id=? ";
        $fetch = PDO_FetchOne($query,array($_POST["channal"],$_COOKIE["userid"]));
        if($fetch>0){
            #有协作权限
            $cooperation = 1;
        }
        else{
            #无协作权限
            $cooperation = 0;
        }
    }
}
else{
    $respond["status"] = 1;
    $respond["message"] = 'error channal id';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

add_edit_event(_SENT_EDIT_,"{$_POST["book"]}-{$_POST["para"]}-{$_POST["begin"]}-{$_POST["end"]}@{$_POST["channal"]}");

PDO_Connect("sqlite:"._FILE_DB_SENTENCE_);

$_id = false;
if( (isset($_POST["id"]) && empty($_POST["id"])) || !isset($_POST["id"]) ){

        # 判断是否已经有了
        $query = "SELECT id FROM sentence WHERE book = ? AND paragraph = ? AND begin = ? AND end = ? AND channal = ? ";
        $_id = PDO_FetchOne($query,array($_POST["book"], $_POST["para"],  $_POST["begin"], $_POST["end"], $_POST["channal"]));
}
else{
    $_id = $_POST["id"];
}



    if($_id==false){
        # 没有id新建
        if($cooperation == 1){
            #有权限
            $query = "INSERT INTO sentence (id, 
                                            parent,
                                            book,
                                            paragraph,
                                            begin,
                                            end,
                                            channal,
                                            tag,
                                            author,
                                            editor,
                                            text,
                                            language,
                                            ver,
                                            status,
                                            strlen,
                                            modify_time,
                                            receive_time,
                                            create_time
                                            ) 
										VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )";
            $stmt = $PDO->prepare($query);
            $newId = UUID::v4();
            $stmt->execute(array($newId,
                                "",
                                $_POST["book"], 
                                $_POST["para"], 
                                $_POST["begin"], 
                                $_POST["end"], 
                                $_POST["channal"], 
                                "", 
                                "[]", 
                                $_COOKIE["userid"],
                                $_POST["text"],
                                $text_lang ,
                                1,
                                $channel_status,
                                mb_strlen($_POST["text"],"UTF-8"),
                                mTime(),
                                mTime(),
                                mTime()
                            ));
            if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                /*  识别错误  */
                $error = PDO_ErrorInfo();
                $respond['message']=$error[2];
                $respond['status']=1;
                echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                exit;
            }
            else{
                # 没错误
                # 更新historay
                #没错误 更新历史记录
                $respond['message']=update_historay($newId,$_COOKIE["userid"] ,$_POST["text"],$_landmark);
                if($respond['message']!==""){
                    $respond['status']=1;
                    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                    exit;
                }
            }
        }
        else{
            #TO DO没权限 插入建议数据
            $respond['message']="没有权限";
            $respond['status']=1;
        }
    }
    else{
        /* 修改现有数据 */
        #判断是否有修改权限
        if($cooperation == 1){
            #有权限
            $query="UPDATE sentence SET text= ?  , strlen = ? , editor = ? , receive_time= ?  , modify_time= ?   where  id= ?  ";
            $stmt  = PDO_Execute($query,
                                            array($_POST["text"],
                                                    mb_strlen($_POST["text"],"UTF-8"), 
                                                    $_COOKIE["userid"] ,
                                                    mTime(),
                                                    mTime(),
                                                    $_id));
            if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                /*  识别错误  */
                $error = PDO_ErrorInfo();
                $respond['message']=$error[2];
                $respond['status']=1;
                echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                exit;
            }
            else{
                #没错误 更新历史记录

                $respond['message']=update_historay($_id,$_COOKIE["userid"] ,$_POST["text"],$_landmark);
                if($respond['message']!==""){
                    $respond['status']=1;
                    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                    exit;                   
                }
            }
        }
        else{
            #TO DO没权限 插入建议数据
            $respond['message']="没有权限";
            $respond['status']=1;
        }
    }


echo json_encode($respond, JSON_UNESCAPED_UNICODE);



?>