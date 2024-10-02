<?php
require_once "../public/function.php";
/*
$dns = ""._FILE_DB_PALITEXT_;
$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

$path="";
$parent = $paragraph;
$deep=0;
$sFirstParentTitle="";
//循环查找父标题 得到整条路径
while($parent>-1){
$query = "select * from pali_text where \"book\" = ? and \"paragraph\" = ? limit 0,1";
$stmt = $dbh->prepare($query);
$stmt->execute(array($book,$parent));
$FetParent = $stmt->fetch(PDO::FETCH_ASSOC);

$toc="<chapter book='{$book}' para='{$parent}' title='{$FetParent["toc"]}'>{$FetParent["toc"]}</chapter>";

if($path==""){
if($FetParent["level"]<100){
$path=$toc;
}
else{
$path="<para book='{$book}' para='{$parent}' title='{$FetParent["toc"]}'>{$paragraph}</para>";
}
}
else{
$path=$toc.$path;
}
if($sFirstParentTitle==""){
$sFirstParentTitle = $FetParent["toc"];
}
$parent = $FetParent["parent"];
$deep++;
if($deep>5){
break;
}
}
$dbh = null;
 */
echo _get_para_path($_GET["book"], $_GET["para"]);
