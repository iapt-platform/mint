<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';
require_once '../hostsetting/function.php';
$respond=array("status"=>0,"message"=>"");

#先查询对此channal是否有权限修改
   PDO_Connect("sqlite:"._FILE_DB_CHANNAL_);
$cooperation = 0;
if(isset($_POST["id"])){
    $query = "SELECT owner FROM channal WHERE id=?";
    $fetch = PDO_FetchOne($query,array($_POST["id"]));
    if($fetch && $fetch==$_COOKIE["userid"]){
        #自己的channal
        $cooperation = 1;
    }
    else{
        $query = "SELECT count(*) FROM cooperation WHERE channal_id= ? and user_id=? ";
        $fetch = PDO_FetchOne($query,array($_POST["id"],$_COOKIE["userid"]));
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
if($cooperation==0){
    $respond["status"] = 1;
    $respond["message"] = 'error 无修改权限';
    echo json_encode($respond, JSON_UNESCAPED_UNICODE);
    exit;
}


$query="UPDATE channal SET name = ? ,  summary = ?,  status = ? , lang = ? , receive_time= ?  , modify_time= ?   where  id = ?  ";
$sth = $PDO->prepare($query);
$sth->execute(array($_POST["name"] , $_POST["summary"], $_POST["status"] , $_POST["lang"] ,  mTime() , mTime() , $_POST["id"]));
$respond=array("status"=>0,"message"=>"");
if (!$sth || ($sth && $sth->errorCode() != 0)) {
	$error = PDO_ErrorInfo();
	$respond['status']=1;
	$respond['message']=$error[2];
}
else{
    // 设置 句子库和逐词译库可见性
    PDO_Connect("sqlite:"._FILE_DB_SENTENCE_);
    $query="UPDATE sentence SET language = ?  , status = ? where  channal = ?  ";
    $sth = PDO_Execute($query,array($_POST["lang"],$_POST["status"],$_POST["id"]));
    if (!$sth || ($sth && $sth->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status']=1;
        $respond['message']=$error[2];
    }
    else{
        // 设置 逐词译库可见性
        PDO_Connect("sqlite:"._FILE_DB_USER_WBW_);
        $query="UPDATE wbw_block SET lang = ?  , status = ? where  channal = ?  ";
        $sth = PDO_Execute($query,array($_POST["lang"],$_POST["status"],$_POST["id"]));
        if (!$sth || ($sth && $sth->errorCode() != 0)) {
            $error = PDO_ErrorInfo();
            $respond['status']=1;
            $respond['message']=$error[2];
        }
    }
}

echo json_encode($respond, JSON_UNESCAPED_UNICODE);
?>