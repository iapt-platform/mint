<?php
#更新一个句子
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$respond=array("status"=>0,"message"=>"");
$respond['book']=$_POST["book"];
$respond['para']=$_POST["para"];
$respond['begin']=$_POST["begin"];
$respond['end']=$_POST["end"];
$respond['channal']=$_POST["channal"];
$respond['text']=$_POST["text"];

#检查是否登陆
if(!isset($_COOKIE["userid"])){
    $respond["status"] = 1;
    $respond["message"] = 'not login';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}

#先查询对此channal是否有权限修改
$cooperation = 0;
if(isset($_POST["channal"])){
   PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
    $query = "SELECT owner FROM channal WHERE id=?";
    $fetch = PDO_FetchOne($query,array($_POST["channal"]));
    if($fetch && $fetch==$_COOKIE["userid"]){
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


PDO_Connect("sqlite:"._FILE_DB_SENTENCE_);
if(isset($_POST["id"])){
    if(empty($_POST["id"])){
        #没有id新建
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
            $stmt->execute(array(UUID::v4(),
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
										  $_POST["lang"],
										  1,
										  7,
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
                $respond['data']=array();
            }
        }
        else{
            #没权限
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
                                                    $_POST["id"]));
            if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
                /*  识别错误  */
                $error = PDO_ErrorInfo();
                $respond['message']=$error[2];
                $respond['status']=1;
                echo json_encode($respond, JSON_UNESCAPED_UNICODE);
                exit;
            }
            else{
                #没错误

            }
        }
        else{
            #没权限 建议
            $respond['message']="没有权限";
            $respond['status']=1;
        }
    }
}
else{
# error
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>