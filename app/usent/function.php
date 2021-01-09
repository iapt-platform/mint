<?php
function update_historay($sent_id,$user_id,$text,$landmark){
    # 更新historay
    PDO_Connect("sqlite:"._FILE_DB_USER_SENTENCE_HISTORAY_);
    $query =  "INSERT INTO sent_historay (sent_id,  user_id,  text,  date, landmark) VALUES (? , ? , ? , ? , ? )";
    $stmt  = PDO_Execute($query,
						array($sent_id,
							 $user_id, 
							 $text ,
							 mTime(),
							 $landmark
							));
    if (!$stmt || ($stmt && $stmt->errorCode() != 0)) {
    /*  识别错误  */
    $error = PDO_ErrorInfo();
        return $error[2];
    }
    else{
    #没错误
        return "";
    }
}
?>