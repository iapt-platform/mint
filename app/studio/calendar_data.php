<?php
//calendar
//定義2019年衛塞節日期，返回julian_day
//$vesakha_2019_date=date_create("2019-05-18");
//返回unix時間戳
//$vesakha_2019_unix=jdtounix($vesakha_2019_jd);
//$now_date=date_create();
//获取当緯度
$atitude=$_GET['atitude'];
//获取当經度
$longitude=$_GET['longitude'];

$sun_info=date_sun_info(strtotime("now"),$atitude,$longitude);
$sun_info_1=date_sun_info(strtotime("+1 day"),$atitude,$longitude);
$Unix_dawn=$sun_info['civil_twilight_begin']*1000;
$Unix_dawn_1=$sun_info_1['civil_twilight_begin']*1000;
$Unix_noon=($sun_info['sunrise']+$sun_info['sunset'])/2*1000;
$Unix_noon_1=($sun_info_1['sunrise']+$sun_info_1['sunset'])/2*1000;
echo "<span id='time_string' >{$Unix_dawn}-{$Unix_noon}-{$Unix_dawn_1}-{$Unix_noon_1}</span>";

//calendar_data.php?atitude=7.7&longitude=80.5

?>