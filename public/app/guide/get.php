<?php
require_once "../config.php";

//获取用户界面语言
if(isset($_COOKIE["language"])){
    $lang = $_COOKIE["language"];
}
else{
    $lang = "en";
}
//查找用户界面语言对应的文件
$filename = _DIR_USERS_GUIDE_."/{$lang}/".$_GET["id"].".md";
if(file_exists($filename)){
    $output["data"]  =  file_get_contents($filename) ;
    $output["id"]  =$_GET["id"];
}
else{
    //如果用户界面不是英语，尝试使用英语文件
    $filename = _DIR_USERS_GUIDE_."/en/".$_GET["id"].".md";
    if($lang != "en" && file_exists($filename)){
        $output["data"]  =  file_get_contents($filename) ;
        $output["id"]  =$_GET["id"];
    }
    else{
        //尝试使用英语文件不成功，报错
        $output["data"]  =  "Error: Can't Find Item In Server.<br> Item Id:{$_GET["id"]} <br> Language:{$lang}";
        $output["id"]  =$_GET["id"];
    }
}

echo json_encode($output,JSON_UNESCAPED_UNICODE);

?>