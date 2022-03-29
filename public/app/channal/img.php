<?php

$img = imagecreate(120,30) or die('create image fail ');
imagecolorallocate($img,255,255,255);
$color = imagecolorallocate($img,0,0,0);
imagestring($img,5,0,0,'300',$color);
header('Content-type:image/gif');
imagegif($img);
imagedestroy($img);
