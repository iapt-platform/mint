<?php
require_once '../path.php';

$tag =  str_getcsv($_GET["tag"],",");//
$arrBookTag=json_decode(file_get_contents("../public/book_tag/en.json"));
$countTag = count($tag);
$output = array();
foreach ($arrBookTag as $bookkey => $bookvalue) {
    $isfind = 0;
    foreach ($tag as $tagkey => $tagvalue) {
        if(strpos($bookvalue->tag,$tagvalue) !== FALSE){
            $isfind++;
        }
    }
    if($isfind==$countTag){
        $output[] = array($bookvalue);
    }
}
echo json_encode($output, JSON_UNESCAPED_UNICODE);
?>