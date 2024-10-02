<?php
require dirname(__FILE__) . '/vendor/autoload.php';


if(isset($_POST['text'])){
    $input = $_POST['text'];
}else{
    $json = file_get_contents('php://input');
    $data = json_decode($json,true);
    $input = $data['text'];
}

$Parsedown = new Parsedown();

header('Content-Type: application/json; charset=utf-8');
echo json_encode([
            'ok'=>true,
            'data'=>$Parsedown->text($input),
            'message'=>'',
        ],
     JSON_UNESCAPED_UNICODE) ;
