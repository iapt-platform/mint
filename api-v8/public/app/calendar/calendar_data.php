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
//$sun_AT=$_GET['sun_AT'];
$date=$_GET['date'];
//$selected_date=$_GET['selected_date'];
//$year=$_GET['year'];
//$month=$_GET['month'];
//$day=$_GET['day'];
$year=date_format(date_create($date),"Y");
$month=date_format(date_create($date),"M");
if($date==null){
	$date=strtotime("now");
}
else{
	$date=strtotime($date);
}

$date_1=$date+24*3600;
//太陽赤緯
//$selected_date_string="{$year}-{$month}-{$day}";
$diff_time=$date-strtotime("{$year}-01-1");//date_timestamp_get(date_create("{$year}-01-1"));
$N=$diff_time/3600/24;
$year_day=366-ceil($year%4/4);
$b=2*pi()*($N-1)/$year_day;
/**
 * [$sun_angle 太陽赤緯，弧度]
 * @var [type]
 */
$sun_angle=0.006918-0.399912*cos($b)+0.070257*sin($b)-0.006758*cos($b*2)+0.000907*sin($b*2)-0.002697*cos($b*3)+0.00148*sin($b*3);
/**
 * [$sun_info description]
 * @var [type]
 */
$sun_info=date_sun_info($date,$atitude,$longitude);
$sun_info_1=date_sun_info($date_1,$atitude,$longitude);
//太陽高度，弧度
$sun_h_degree=pi()/2-abs(($atitude/180*pi()-$sun_angle));
//蒙氣差(horizontal refraction)修正值
$hori_ref=37/60/180*2*pi();
$hori_ref_real=asin(sin($hori_ref)/sin($sun_h_degree));
//$delta_t=$hori_ref_real*(12*3600/pi());
$delta_t_A=$sun_info['civil_twilight_begin']-$sun_info['astronomical_twilight_begin']+$sun_info['nautical_twilight_begin']-$sun_info['sunrise'];
$delta_t=$delta_t_A;
/**
 * [$Unix_dawn description]
 * @var [type]
 */
$Unix_dawn=($sun_info['civil_twilight_begin']-$delta_t)*1000;
$Unix_dawn_1=($sun_info_1['civil_twilight_begin']-$delta_t)*1000;
$Unix_noon=($sun_info['sunrise']+$sun_info['sunset'])/2*1000;
$Unix_noon_1=($sun_info_1['sunrise']+$sun_info_1['sunset'])/2*1000;
echo "{$Unix_dawn}-{$Unix_noon}-{$Unix_dawn_1}-{$Unix_noon_1}-{$sun_h_degree}-{$delta_t}";


//http://127.0.0.1/app/calendar_data.php?atitude=7.738562&longitude=80.519675&date=2020-05-11&year=2020&month=05&day=11
?>