<?php
/*
get user sentence from db
*/
require_once "../path.php";
require_once "../public/_pdo.php";
require_once "../public/function.php";

$para = $_POST["para"];

$paraList = json_decode($para);
$output = array();

$dns = "sqlite:"._FILE_DB_PALI_SENTENCE_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);  

$query="SELECT * FROM sentence WHERE (book = ?  AND paragraph = ?  ) ";
$stmt = $dbh->prepare($query);
foreach ($sentList as $key => $value) {
    # code...
    $stmt->execute(array($value->book,$value->para));
    $Fetch = $stmt->fetchAll(PDO::FETCH_ASSOC);
         $sent["info"]=array("book"=>$value->book,"para"=>$value->para);
        $sent["data"]=$valueSent;
        $output[] = $sent;  
}

    echo json_encode($output, JSON_UNESCAPED_UNICODE);

?>