<?php
require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../public/function.php';

$respond=array("status"=>0,"message"=>"");
PDO_Connect("sqlite:"._FILE_DB_TERM_);

if($_POST["id"]!=""){
    $query="UPDATE term SET meaning= ? ,other_meaning = ? , tag= ? ,channal = ? ,  language = ? , note = ? , receive_time= ?, modify_time= ?   where guid= ? ";
    $stmt = @PDO_Execute($query,array($_POST["mean"],
                                        $_POST["mean2"],
                                        $_POST["tag"],
                                        $_POST["channal"],
                                        $_POST["language"],
                                        $_POST["note"],
                                        mTime(),
                                        mTime(),
                                        $_POST["id"]
                                                            ));
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
        $error = PDO_ErrorInfo();
        $respond['status']=1;
        $respond['message']=$error[2].$query;
    }
    else{
        $respond['status']=0;
        $respond['message']=$_POST["word"];
    }		
}
else{

}
    

    echo json_encode($respond, JSON_UNESCAPED_UNICODE);

?>