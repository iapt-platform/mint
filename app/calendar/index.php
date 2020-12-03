<?php
//require '../../app/checklogin.inc';
require '../public/config.php';
require_once '../public/load_lang.php';

if(isset($_GET["language"])){
	$currLanguage=$_GET["language"];
	$_COOKIE["language"]=$currLanguage;
}
else{
	if(isset($_COOKIE["language"])){
		$currLanguage=$_COOKIE["language"];
	}
	else{
		$currLanguage="en";
		$_COOKIE["language"]=$currLanguage;
	}
}

//load language file
/*
if(file_exists($dir_language.$currLanguage.".php")){
	require $dir_language.$currLanguage.".php";
}
else{
	require $dir_language."default.php";
}
require __DIR__."/../../app/language/default.php";
*/
if(isset($_GET["device"])){
	$currDevice=$_GET["device"];
}
else{
	if(isset($_COOKIE["device"])){
		$currDevice=$_COOKIE["device"];
	}
	else{
		$currDevice="computer";
	}
}
//修改页面编码
//header("content-type:text/html;charset=utf-8");
//获取当前年
$year=$_GET['y']?$_GET['y']:date('Y');
//获取当年月
$month=$_GET['m']?$_GET['m']:date('m');
//获取当前日
//$selected_date=$_GET['d']?$_GET['d']:date('j');

$dir_app="../studio/";

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<script language="javascript" src="../studio/charcode/sinhala.js"></script>
	<script language="javascript" src="../studio/charcode/myanmar.js"></script>
	<script language="javascript" src="../studio/charcode/unicode.js"></script>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="<?php echo $dir_app; ?>css/style.css"/>
	<link type="text/css" rel="stylesheet" href="<?php echo $dir_app; ?>css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="<?php echo $dir_app; ?>css/style.css"/>
	<title><?php echo $_local->gui->BE;?></title>
	<script src="<?php echo $dir_app; ?>js/jquery-3.3.1.min.js"></script>
<!--	<script language="javascript" src="<?php echo $dir_app; ?>charcode/sinhala.js"></script>
	<script language="javascript" src="<?php echo $dir_app; ?>charcode/myanmar.js"></script>
	<script language="javascript" src="<?php echo $dir_app; ?>charcode/unicode.js"></script>
-->
<script>
	<?php 
	//加载js语言包
	require_once '../public/load_lang_js.php';
	?>
</script>
<style type="text/css">
.BE_icon_span{
	width: 7em;
    display: inline-block;
}
#BE_icon{
	font-size: 100%;
}
.td_today{
	margin: auto;
    background: silver;
    color: black;
	width: 2em;
}
.new_moon_uposatha{
	background:black;
	border-radius: 2em;
	width: 2em;
	margin: auto;
}
.full_moon_uposatha{
	background:orange;
	border-radius: 2em;
	width: 2em;
	margin: auto;
}
.table_body{
	width:700px;
	border:1px;
	font-size: 60%;
	width: 95%;
	max-width: 45em;
	line-height: 2em;
	text-align: center;
	margin: 20px 0px;
	border-collapse: collapse;
}
.table_line{
	display: flex;
}
.table_column{
	border:solid;
	border-width: thin;
	flex:1;
}
input[type="number"]{
	width: 3em;
}
input[type="date"]{
	width: 10em;
}
</style>	


	<script type="text/javascript">
	
		var g_device = "computer";
		var strSertch = location.search;
		if(strSertch.length>0){
			strSertch = strSertch.substr(1);
			var sertchList=strSertch.split('&');
			for ( i in sertchList){
				var item = sertchList[i].split('=');
				if(item[0]=="device"){
					g_device=item[1];
				}
			}
		}
		if(g_device=="mobile"){
			g_is_mobile=true;
		}
		else{
			g_is_mobile=false;
		}


			var g_language="en";
function setCookie(c_name,value,expiredays)
{
	var exdate=new Date()
	exdate.setDate(exdate.getDate()+expiredays)
	document.cookie=c_name+ "=" +escape(value)+((expiredays==null) ? "" : "; expires="+exdate.toGMTString())
}
var pali_num_str=[
{ "id":"1" , "value":"eka" },
{ "id":"2" , "value":"dve" },
{ "id":"3" , "value":"ti" },
{ "id":"4" , "value":"catu" },
{ "id":"5" , "value":"pañca" },
{ "id":"6" , "value":"cha" },
{ "id":"7" , "value":"satta" },
{ "id":"8" , "value":"aṭṭha" },
{ "id":"9" , "value":"nava" },
{ "id":"10" , "value":"dasa" },
{ "id":"11" , "value":"ekādasa" },
{ "id":"12" , "value":"dvādasa " },
{ "id":"13" , "value":"terasa" },
{ "id":"14" , "value":"cuddasa" },
{ "id":"15" , "value":"paṇṇarasa" },
{ "id":"16" , "value":"soḷasa" },
{ "id":"17" , "value":"sattarasa" },
{ "id":"18" , "value":"aṭṭharasa" },
{ "id":"19" , "value":"ekūna-vīsati" },
{ "id":"20" , "value":"vīsati" },
{ "id":"21" , "value":"eka-vīsati" },
{ "id":"22" , "value":"dve-vīsati" },
{ "id":"23" , "value":"te-vīsati" },
{ "id":"24" , "value":"catu-vīsati" },
{ "id":"25" , "value":"pañca-vīsati" },
{ "id":"26" , "value":"cha-bbīsati" },
{ "id":"27" , "value":"satta-vīsati" },
{ "id":"28" , "value":"aṭṭha-vīsati" },
{ "id":"29" , "value":"ekūna-tiṃsati" },
{ "id":"30" , "value":"tiṃsati" },
{ "id":"31" , "value":"eka-tiṃsati" },
{ "id":"32" , "value":"dvattiṃsati" },
{ "id":"33" , "value":"tettiṃsati" },
{ "id":"34" , "value":"catuttiṃsati" },
{ "id":"35" , "value":"pañcatiṃsati" },
{ "id":"36" , "value":"chattiṃsati" },
{ "id":"37" , "value":"sattatiṃsati" },
{ "id":"38" , "value":"aṭṭhatiṃsati" },
{ "id":"39" , "value":"ekūna-cattālīsati" },
{ "id":"40" , "value":"cattālīsati" },
{ "id":"41" , "value":"eka-cattālīsati" },
{ "id":"42" , "value":"dve-cattālīsati" },
{ "id":"43" , "value":"ti-cattālīsati" },
{ "id":"44" , "value":"catu-cattālīsati" },
{ "id":"45" , "value":"pañca-cattālīsati" },
{ "id":"46" , "value":"cha-cattālīsati" },
{ "id":"47" , "value":"satta-cattālīsati" },
{ "id":"48" , "value":"aṭṭha-cattālīsati" },
{ "id":"49" , "value":"ekūna-paññāsā" },
{ "id":"50" , "value":"paññāsā" },
{ "id":"51" , "value":"eka-paññāsā" },
{ "id":"52" , "value":"dve-paññāsā" },
{ "id":"53" , "value":"ti-paññāsā" },
{ "id":"54" , "value":"catu-paññāsā" },
{ "id":"55" , "value":"pañca-paññāsā" },
{ "id":"56" , "value":"cha-paññāsā" },
{ "id":"57" , "value":"satta-paññāsā" },
{ "id":"58" , "value":"aṭṭha-paññāsā" },
{ "id":"59" , "value":"ekūna-saṭṭhi" },
{ "id":"60" , "value":"saṭṭhi" },
{ "id":"61" , "value":"eka-saṭṭhi" },
{ "id":"62" , "value":"dve-saṭṭhi" },
{ "id":"63" , "value":"ti-saṭṭhi" },
{ "id":"64" , "value":"catu-saṭṭhi" },
{ "id":"65" , "value":"pañca-saṭṭhi" },
{ "id":"66" , "value":"cha-saṭṭhi" },
{ "id":"67" , "value":"satta-saṭṭhi" },
{ "id":"68" , "value":"aṭṭha-saṭṭhi" },
{ "id":"69" , "value":"ekūna-sattati" },
{ "id":"70" , "value":"sattati" },
{ "id":"71" , "value":"eka-sattati" },
{ "id":"72" , "value":"dve-sattati" },
{ "id":"73" , "value":"ti-sattati" },
{ "id":"74" , "value":"catu-sattati" },
{ "id":"75" , "value":"pañca-sattati" },
{ "id":"76" , "value":"cha-sattati" },
{ "id":"77" , "value":"satta-sattati" },
{ "id":"78" , "value":"aṭṭha-sattati" },
{ "id":"79" , "value":"ekūnāsīti" },
{ "id":"80" , "value":"asīti" },
{ "id":"81" , "value":"eka-asīti" },
{ "id":"82" , "value":"dve-asīti" },
{ "id":"83" , "value":"ti-asīti" },
{ "id":"84" , "value":"catu-asīti" },
{ "id":"85" , "value":"pañca-asīti" },
{ "id":"86" , "value":"cha-asīti" },
{ "id":"87" , "value":"satta-asīti" },
{ "id":"88" , "value":"aṭṭha-asīti" },
{ "id":"89" , "value":"ekūna-navuti" },
{ "id":"90" , "value":"navuti" },
{ "id":"91" , "value":"eka-navuti" },
{ "id":"92" , "value":"dve-navuti" },
{ "id":"93" , "value":"ti-navuti" },
{ "id":"94" , "value":"catu-navuti" },
{ "id":"95" , "value":"pañca-navuti" },
{ "id":"96" , "value":"cha-navuti" },
{ "id":"97" , "value":"satta-navuti" },
{ "id":"98" , "value":"aṭṭha-navuti" },
{ "id":"99" , "value":"ekūna-sata" },
{ "id":"100" , "value":"sata" },
{ "id":"200" , "value":"dvi-sata" },
{ "id":"300" , "value":"ti-sata" },
{ "id":"400" , "value":"catu-sata" },
{ "id":"500" , "value":"pañca-sata" },
{ "id":"600" , "value":"cha-sata" },
{ "id":"700" , "value":"satta-sata" },
{ "id":"800" , "value":"aṭṭha-sata" },
{ "id":"900" , "value":"nava-sata" },
{ "id":"1000" , "value":"sahassa" },
{ "id":"2000" , "value":"dve-sahassa" },
{ "id":"3000" , "value":"ti-sahassa" },
{ "id":"4000" , "value":"catu-sahassa" },
{ "id":"5000" , "value":"pañca-sahassa" }
]
var pali_num_str_pl=[
//{ "id":"0" , "value":"na" },
//{ "id":"1" , "value":"eka" },
{ "id":"2" , "value":"dve" },
{ "id":"3" , "value":"tīṇi" },
{ "id":"4" , "value":"cattāri" },
{ "id":"5" , "value":"pañca" },
{ "id":"6" , "value":"cha" },
{ "id":"7" , "value":"satta" },
{ "id":"8" , "value":"aṭṭha" },
{ "id":"9" , "value":"nava" },
{ "id":"10" , "value":"dasa" },
{ "id":"11" , "value":"ekādasa" },
{ "id":"12" , "value":"dvādasa " },
{ "id":"13" , "value":"terasa" },
{ "id":"14" , "value":"cuddasa" },
{ "id":"15" , "value":"paṇṇarasa" },
{ "id":"16" , "value":"soḷasa" },
{ "id":"17" , "value":"sattarasa" },
{ "id":"18" , "value":"aṭṭharasa" },
{ "id":"19" , "value":"ekūna-vīsati" },
{ "id":"20" , "value":"vīsati" },
{ "id":"21" , "value":"eka-vīsati" },
{ "id":"22" , "value":"dve-vīsati" },
{ "id":"23" , "value":"te-vīsati" },
{ "id":"24" , "value":"catu-vīsati" },
{ "id":"25" , "value":"pañca-vīsati" },
{ "id":"26" , "value":"cha-bbīsati" },
{ "id":"27" , "value":"satta-vīsati" },
{ "id":"28" , "value":"aṭṭha-vīsati" },
{ "id":"29" , "value":"ekūna-tiṃsati" }
]
var pali_year_name=[
{ "id":"0" , "value":"sappa" , "icon":"🐍"},
//🐀🐃🐄🐏🐑       
//🐭🐮🐯🐰🐲🐍🐴🐵🐔🐶🐷 
{ "id":"1" , "value":"assa" , "icon":"🐎"},
{ "id":"2" , "value":"aja" , "icon":"🐐"},
{ "id":"3" , "value":"kapi" , "icon":"🐒"},
{ "id":"4" , "value":"kukkuṭa" , "icon":"🐓"},
{ "id":"5" , "value":"soṇa" , "icon":"🐕"},
{ "id":"6" , "value":"sūkara" , "icon":"🐖"},
{ "id":"7" , "value":"mūsika" , "icon":"🐁"},
{ "id":"8" , "value":"vasabha" , "icon":"🐂"},
{ "id":"9" , "value":"vyaggaha" , "icon":"🐅"},
{ "id":"10" , "value":"sasa" , "icon":"🐇"},
{ "id":"11" , "value":"nāga" , "icon":"🐉"}
]
var pali_month_name=[
{ "id":"1" , "value":"jeṭṭha" , "season":"gimhāna" , "season_icon":"☀"},//5.X-四-十五-心
{ "id":"2" , "value":"asāḷha" , "season":"gimhāna" , "season_icon":"☀"},//6.X-五-十五、十六-箕、斗
{ "id":"3" , "value":"sāvana" , "season":"vassāna" , "season_icon":"☔"},//7.X-六-十五-女
{ "id":"4" , "value":"poṭṭhapāda" , "season":"vassāna" , "season_icon":"☔"},//8.x-七-十五、十六-室、壁
{ "id":"5" , "value":"assajuja" , "season":"vassāna" , "season_icon":"☔"},//9.X-八-十五-樓
{ "id":"6" , "value":"kattika" , "season":"vassāna" , "season_icon":"☔"},//10.X-九-十五-昂
{ "id":"7" , "value":"māgasira" , "season":"hemanta" , "season_icon":"❄"},//11.X-十-十五-觜
{ "id":"8" , "value":"phussa" , "season":"hemanta" , "season_icon":"❄"},//12.X-十一-十五-鬼
{ "id":"9" , "value":"māgha" , "season":"hemanta" , "season_icon":"❄"},//1.X-十二-十五-星
{ "id":"10" , "value":"phagguna" , "season":"hemanta" , "season_icon":"❄"},//2.X-正月-十四、十五-張、異
{ "id":"11" , "value":"citta" , "season":"gimhāna" , "season_icon":"☀"},//3.X-二月-十五-角
{ "id":"12" , "value":"vesākha" , "season":"gimhāna" , "season_icon":"☀"}//4.X-三月-十五-氐
]
var pali_date_name=[
{ "id":"1" , "value":"paṭhamaṃ" },
{ "id":"2" , "value":"dutiyaṃ" },
{ "id":"3" , "value":"tatiyaṃ" },
{ "id":"4" , "value":"catutthaṃ" },
{ "id":"5" , "value":"pañcamaṃ" },
{ "id":"6" , "value":"chaṭṭhamaṃ" },
{ "id":"7" , "value":"sattamaṃ" },
{ "id":"8" , "value":"aṭṭhamaṃ" },
{ "id":"9" , "value":"navamaṃ" },
{ "id":"10" , "value":"dasamaṃ" },
{ "id":"11" , "value":"ekādasamaṃ" },
{ "id":"12" , "value":"dvādasamaṃ" },
{ "id":"13" , "value":"terasamaṃ" },
{ "id":"14" , "value":"cuddasamaṃ" },
{ "id":"15" , "value":"paṇṇarasamaṃ" }
]
var pali_week_day_name=[
{ "id":"0" , "value":"ravi" ,"icon":"☀"},
{ "id":"1" , "value":"canda" ,"icon":"🌙"},
{ "id":"2" , "value":"bhumma" ,"icon":""},//土
{ "id":"3" , "value":"budha" ,"icon":""},//水星
{ "id":"4" , "value":"guru" ,"icon":""},//木星
{ "id":"5" , "value":"sukka" ,"icon":"♀"},//金星
{ "id":"6" , "value":"sora" ,"icon":"♂"}//火星
]
var g_Unix_now=0;
function startTime(){
var today=new Date()
var h=today.getHours()
var m=today.getMinutes()
var s=today.getSeconds()
// add a zero in front of numbers<10
h=checkTime(h)
m=checkTime(m)
s=checkTime(s)
document.getElementById('clock_string').innerHTML=h+":"+m+":"+s
g_Unix_now=today.getTime()

t=setTimeout('startTime()',500)

}

function checkTime(i){
if (i<10) 
  {i="0" + i}
  return i
}

	</script>

</head>
<body class="indexbody" onload="startTime()">
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div class="toolgroup1">
				
				<span><?php echo $_local->gui->language_select;?></span>
	<?php
		echo "<select id='id_language' name='menu' onchange=\"menuLangrage(this,".$year.",".$month.")\">";
		echo "<option value='pali' >Pāḷi Roman</option>";
		echo "<option value='en' >English</option>";
		echo "<option value='si' >සිංහල</option>";
		echo "<option value='my' >myanmar</option>";
		echo "<option value='zh-cn' >简体中文</option>";
		echo "<option value='zh-tw' >繁體中文</option>";
		echo "</select>";
	?>
			</div>
		</div>	
		<!--tool bar end -->
<script>
	document.getElementById("id_language").value="<?php echo($currLanguage); ?>";
</script>
<?php
//獲取今天是幾號
$cur_date=date('j');
//获取当前月多少天
$days=date('t',strtotime("{$year}-{$month}-1"));
//選定日
if($cur_date>$days){
	$selected_date=1;
}
else{
	$selected_date=$cur_date;
}

//当前一号周几
$week=date('w',strtotime("{$year}-{$month}-1"));
//居中
echo "<br/><br/><br/>";
echo "<div style='display:flex; flex-direction: column;'>";
echo "<center style='display: flex;flex: 3;flex-direction: column;padding: 10px;'>";
//設定vesakha日
$vesakha_day=date_create("2019-5-18");
$vesakha_day_1=date_sub($vesakha_day,date_interval_create_from_date_string("-354 days"));
$vesakha_day=date_create("2019-5-18");
//當前日差值
$diff=date_diff($vesakha_day,date_create("{$year}-{$month}-{$selected_date}"));
$diff_1=date_diff($vesakha_day_1,date_create("{$year}-{$month}-{$selected_date}"));
$days_diff=$diff->format("%R%a");
$days_diff_1=$diff_1->format("%R%a");
if($days_diff_1>0){
	$days_diff=$days_diff_1;
}

//當前年差值
if($diff->format("%R")=="+" && $diff_1->format("%R")=="+"){
	$years_diff_total=544-1+date_format($vesakha_day_1,"Y");
}
else if($diff->format("%R")=="+" && $diff_1->format("%R")=="-"){
	$years_diff_total=544-1+date_format($vesakha_day,"Y");

}
else{
	$years_diff_total=544-2+date_format($vesakha_day,"Y");
}

//计算上个月
if($month==1){
    $prevyear=$year-1;
    $prevmonth=12;
}
else{
	$prevyear=$year;
	$prevmonth=$month-1;
}
//计算下个月
if($month==12){
    $nextyear=$year+1;
    $nextmonth=1;
}
else{ 
    $nextyear=$year;
    $nextmonth=$month+1;
}
//生成年份名稱
if($currLanguage=="en"){
	$year_text=$year."/";
}
else{
	$year_text="{$year}".$_local->gui->saṃvacchara;//年
}
//生成月份名稱
if($currLanguage=="en"){
	$month_text=date('F',strtotime("{$year}-{$month}-1"));
}
else{
	$month_text="{$month}".$_local->gui->māsa;//月
}
//输出表头
echo "<h2 style='font-size: 80%;'>";
echo "<a href='index.php?y={$prevyear}&m={$prevmonth}'>";
echo $_local->gui->pubba_māsa;//上一月
echo "</a>|";
echo $year_text;
echo $month_text;
echo "|<a href='index.php?y={$nextyear}&m={$nextmonth}'>";
echo $_local->gui->pacchā_māsa;//下一月
echo "</a></h2>";


//输出日期表格
echo "<table class='table_body' >";
echo "<tr class='table_line'>";
echo "<th class='table_column'>".$_local->gui->ravi."</th>";
echo "<th class='table_column'>".$_local->gui->canda."</th>";
echo "<th class='table_column'>".$_local->gui->bhumma."</th>";
echo "<th class='table_column'>".$_local->gui->budha."</th>";
echo "<th class='table_column'>".$_local->gui->guru."</th>";
echo "<th class='table_column'>".$_local->gui->sukka."</th>";
echo "<th class='table_column'>".$_local->gui->sora."</th>";
echo "</tr>";

//铺表格
for($i=1-$week; $i <=$days ;){ 
    echo "<tr class='table_line'>";
    for ($j=0; $j < 7; $j++){ 
        if ($i>$days || $i<1){
            echo "<td class='table_column'>&nbsp;</td>";
        }
        else if($i==$cur_date && $month==date('m')){
        	$days_diff_X=$days_diff-$selected_date+$i;
        	$cur_years_diff_total=$years_diff_total;
        	$cur_days_diff=$days_diff_X;
        	$cur_week_day=$j;
			echo "<td class='table_column' id='td_{$i}' onclick=\"pali_date($i,$days,$years_diff_total,$days_diff_X,$j,'".$currLanguage."',g_coordinate_this)\")' ><div class='td_today'>{$i}</div>";
			echo "</td>";
			if($i==$days){
				$key_days_num=$days_diff_X;
			}
        }
        else if($i==$selected_date){
        	$days_diff_X=$days_diff-$selected_date+$i;
        	$cur_years_diff_total=$years_diff_total;
        	$cur_days_diff=$days_diff_X;
        	$cur_week_day=$j;
			echo "<td class='table_column' id='td_{$i}' onclick=\"pali_date($i,$days,$years_diff_total,$days_diff,$j,'".$currLanguage."',g_coordinate_this)\">{$i}</td>";
			if($i==$days){
				$key_days_num=$days_diff;
			}
        }
        else{
        	$days_diff_X=$days_diff-$selected_date+$i;
        	$cur_years_diff_total=$years_diff_total;
        	$cur_days_diff=$days_diff_X;
        	$cur_week_day=$j;
			echo "<td class='table_column' id='td_{$i}' onclick=\"pali_date($i,$days,$years_diff_total,$days_diff_X,$j,'".$currLanguage."',g_coordinate_this)\">{$i}</td>";
			if($i==$days){
				$key_days_num=$days_diff_X;
			}
        }
        $i++;
    }
    echo "</tr>";
}
echo "</table>";
echo "<div id='position_change'>";
echo "<span id='selected_position_string'>".$_local->gui->loading."</span>";
echo "<button onclick='getLocation()' style='font-size: 100%; padding: 2px 6px;'>";
echo "<svg class='icon' style='min-width: 1.8em; min-height: 1.8em;' >";
echo "<path d='M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0 0 13 3.06V1h-2v2.06A8.994 8.994 0 0 0 3.06 11H1v2h2.06A8.994 8.994 0 0 0 11 20.94V23h2v-2.06A8.994 8.994 0 0 0 20.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z'/>";
echo "</svg>".$_local->gui->my_loc."</button>";//自动定位
echo "<button onclick='set_display(\"position_change\",\"position_input\")' style='display: none;'>".$_local->gui->change_position."</button>";//變更地點
echo "</div>";
echo "<div id='position_input' style='display:none;'><span>";
echo $_local->gui->atitude;//緯度
echo "：<input id='AT_selected_°' type='number' min='0' max='90' /> ° ";
echo "<input id='AT_selected_’' type='number' min='0' max='60' /> ’ ";
echo "<input id='AT_selected_”' type='number' min='0' max='60' /> ” ";
echo "<select id='selected_NS'>";
echo "<option value='+' >N</option>";
echo "<option value='-' >S</option>";
echo "</select>";
echo "</span><span>";
echo $_local->gui->longitude;//經度
echo "：<input id='LT_selected_°' type='number' min='0' max='180' /> ° ";
echo "<input id='LT_selected_’' type='number' min='0' max='60' /> ’ ";
echo "<input id='LT_selected_”' type='number' min='0' max='60' /> ” ";
echo "<select id='selected_WE'>";
echo "<option value='+' >E</option>";
echo "<option value='-' >W</option>";
echo "</select>";
echo "</span>";
echo "<div id='position_selected'>";
echo "</div>";
echo "<div><button onclick='confirm()'>";
echo $_local->gui->confirm;//確認
echo "</button></div>";
echo "</div>";
echo "<div style='display:flex; margin: 20px;'>";
echo "<div style='display:grid; flex:3'>";
echo "<span id='time_A_string_X' style='font-size: 100%;'></span>";
echo "<span id='clock_string_X' style='font-size: 100%;'></span>";
echo "<span id='time_B_string_X' style='font-size: 100%; '></span>";
echo "</div>";
echo "<div id='time_sting_div' style='display:grid; flex:2'>";
echo "<span id='time_A_string' style='font-size: 100%; '></span>";
echo "<span id='clock_string' style='font-size: 100%; '></span>";
echo "<span id='time_B_string' style='font-size: 100%; '></span>";
echo "</div>";
echo "<div style='display:grid; flex:6'>";
echo "<span id='kala_judge' style='font-size: 100%; '></span>";
echo "<span id='kala_inst_1' style='font-size: 100%; '></span>";
echo "<span id='kala_inst_2' style='font-size: 100%; '></span>";
echo "</div>";
echo "</div>";
echo "<div id='hori_ref' style='font-size: 100%; '></div>";

//飛機過午問題
echo "<div style='display:flex; font-size: 100%;'>";
//起飛
echo "<div style='flex:3;'>";
echo "<div id='departure_position_result'>";
echo "<span id='departure_position_string'></span>";
echo "<button onclick='set_display(\"departure_position_result\",\"departure_position_input\")'>";
echo $_local->gui->departure_in_detail;//起飛詳情
echo "</button>";
echo "</div>";
echo "<div id='departure_position_input' style='display:none;'>";
//起飛時間
echo $_local->gui->date;//日期
echo "：<input id='departure_date' type='date' />";
echo $_local->gui->time;//時間
echo "：<input id='departure_time' type='time' /><br>";
//起飛地點
echo "<span>";
echo $_local->gui->atitude;//緯度
echo "：<input id='AT_departure_°' type='number' min='0' max='90' /> ° ";
echo "<input id='AT_departure_’' type='number' min='0' max='60' /> ’ ";
echo "<input id='AT_departure_”' type='number' min='0' max='60' /> ” ";
echo "<select id='departure_NS'>";
echo "<option value='+' >N</option>";
echo "<option value='-' >S</option>";
echo "</select>";
echo "</span><br><span>";
echo $_local->gui->longitude;//經度
echo "：<input id='LT_departure_°' type='number' min='0' max='180' /> ° ";
echo "<input id='LT_departure_’' type='number' min='0' max='60' /> ’ ";
echo "<input id='LT_departure_”' type='number' min='0' max='60' /> ” ";
echo "<select id='departure_WE'>";
echo "<option value='+' >E</option>";
echo "<option value='-' >W</option>";
echo "</select>";
echo "</span>";
echo "<div><button onclick='air_confirm(\"departure\")'>";
echo $_local->gui->confirm;//確認
echo "</button></div></div></div>";

//降落
echo "<div style='flex:3;'>";
echo "<div id='arrival_position_result'>";
echo "<span id='arrival_position_string'></span>";
echo "<button onclick='set_display(\"arrival_position_result\",\"arrival_position_input\")'>";
echo $_local->gui->arrival_in_detail;//降落詳情
echo "</button>";
echo "</div>";
echo "<div id='arrival_position_input' style='display:none;'>";
//降落時間
echo $_local->gui->date;//日期
echo "：<input id='arrival_date' type='date' />";
echo $_local->gui->time;//時間
echo "：<input id='arrival_time' type='time' /><br>";
//降落地點
echo "<span>";
echo $_local->gui->atitude;//緯度
echo "：<input id='AT_arrival_°' type='number' min='0' max='90' /> ° ";
echo "<input id='AT_arrival_’' type='number' min='0' max='60' /> ’ ";
echo "<input id='AT_arrival_”' type='number' min='0' max='60' /> ” ";
echo "<select id='arrival_NS'>";
echo "<option value='+' >N</option>";
echo "<option value='-' >S</option>";
echo "</select>";
echo "</span><br><span>";
echo $_local->gui->longitude;//經度
echo "：<input id='LT_arrival_°' type='number' min='0' max='180' /> ° ";
echo "<input id='LT_arrival_’' type='number' min='0' max='60' /> ’ ";
echo "<input id='LT_arrival_”' type='number' min='0' max='60' /> ” ";
echo "<select id='arrival_WE'>";
echo "<option value='+' >E</option>";
echo "<option value='-' >W</option>";
echo "</select>";
echo "</span>";
echo "<div><button onclick='air_confirm(\"arrival\")'>";
echo $_local->gui->confirm;//確認
echo "</button></div></div></div>";

echo "</div>";

echo "<span id='key_num_string' style='display: none; '>{$key_days_num}-{$days}-{$year}-{$month}</span>";
if($cur_years_diff_total!=null && $cur_days_diff!=null && $cur_week_day!=null){
echo "<span id='cur_day_string' style='display: none; '>{$cur_date}-{$days}-{$cur_years_diff_total}-{$cur_days_diff}-{$cur_week_day}-{$currLanguage}</span>";
}
echo "</center>";



date_default_timezone_set("Asia/Rangoon");/*
$Unix_now_time=strtotime("now");
$sun_info=date_sun_info(strtotime("now"),7.738562,80.519675);
$sun_info_1=date_sun_info(strtotime("+1 day"),7.738562,80.519675);
$Unix_dawn=$sun_info['civil_twilight_begin']*1000;
$Unix_dawn_1=$sun_info_1['civil_twilight_begin']*1000;
$Unix_noon=($sun_info['sunrise']+$sun_info['sunset'])/2*1000;
$Unix_noon_1=($sun_info_1['sunrise']+$sun_info_1['sunset'])/2*1000;*/
echo "<span id='time_string'  style='display: none;'></span>";//
echo "<span id='air_time_string'  style='display: none;'></span>";//

//echo date("H:i:s",$Unix_dawn)."<br/>";
//echo date("H:i:s",$Unix_noon)."<br/>";

echo "<div id='sun_AT' style='display:none;'>{$month}-{$selected_date}</div>";

//echo "<button onclick='set_uposatha_day()'>set uposatha</button>";
echo "<div id='pali_era' style='font-size: 150%;display: flex;flex: 7;padding: 10px;flex-direction: column;'>";
echo "<div id='pali_era_graph' style='display:grid;flex:3;'></div>";
echo "<div id='pali_era_pali' style='display:grid;flex:7;'></div>";
echo "</div>";
//echo date_format($vesakha_day,"Y-m-d")."|".date_format($vesakha_day_1,"Y-m-d")
echo "</div>";


?>
<script language="javascript">
//設定經緯度
var g_coordinate_this=new Object;
getLocation();
g_coordinate_this=get_coordinate_num("selected");
	/*if(g_coordinate_this.AT==0 || g_coordinate_this.LT==0){
		g_coordinate_this.AT=7.738562;
		g_coordinate_this.LT=80.519675;
	}
*/

function angle_trans(angle){
var angle_str="";
var num_d=Math.floor(angle);
var num_m=Math.floor((angle-num_d)*60);
var num_s=Math.round((angle-num_d)*60-num_m);
	if(num_d!=0){
		angle_str+=num_d+"°";
	}
	if(num_m!=0){
		angle_str+=num_m+"’";
	}
	if(num_s!=0){
		angle_str+=num_s+"”";
	}
	return(angle_str);
}
function dawn_noon_display(){
	var Unix_string=document.getElementById('time_string').innerHTML
	var Unix_Array=Unix_string.split("-");
	var time_A=new Date();
	var time_B=new Date();
	$("#clock_string_X").html(gLocal.gui.now_time+"：")
	var note_str="";

	var sun_hd_str=angle_trans(Unix_Array[4]/Math.PI*180);
	var hori_ref_time=Math.floor(Unix_Array[5]/60)+gLocal.gui.mins;
		hori_ref_time+=Math.floor(Unix_Array[5]%60)+gLocal.gui.sec;
		note_str+=gLocal.gui.note+"：";
		note_str+=gLocal.gui.sun_height_degree+" "+sun_hd_str+"；";
		note_str+=gLocal.gui.hori_ref_time+" "+hori_ref_time;
		$("#hori_ref").html(note_str);
	if(g_Unix_now<Unix_Array[0]){
		time_A.setTime(Unix_Array[1])
		time_B.setTime(Unix_Array[0])
		$("#time_A_string_X").html(gLocal.gui.noon_time+"：");
		$("#time_A_string").html(set_time_string(time_A));
		$("#time_B_string_X").html(gLocal.gui.twilight_time+"：");
		$("#time_B_string").html(set_time_string(time_B));
		$("#kala_judge").html("【"+gLocal.gui.vikala+"】");
		$("#kala_inst_1").html(gLocal.gui.no_string+gLocal.gui.eat);
		$("#kala_inst_2").html(gLocal.gui.need_inform+gLocal.gui.no_string+gLocal.gui.gama_entry);

	}
	else if(g_Unix_now>=Unix_Array[0] && g_Unix_now<Unix_Array[1]){
		time_A.setTime(Unix_Array[0])
		time_B.setTime(Unix_Array[1])
		$("#time_A_string_X").html(gLocal.gui.twilight_time+"：");
		$("#time_A_string").html(set_time_string(time_A));
		$("#time_B_string_X").html(gLocal.gui.noon_time+"：");
		$("#time_B_string").html(set_time_string(time_B));
		$("#kala_judge").html("【"+gLocal.gui.kala+"】");
		$("#kala_inst_1").html(gLocal.gui.yes_string+gLocal.gui.eat);
		$("#kala_inst_2").html(gLocal.gui.need_inform+gLocal.gui.yes_string+gLocal.gui.gama_entry);
	}
	else if(g_Unix_now>=Unix_Array[1] && g_Unix_now<Unix_Array[2]){
		time_A.setTime(Unix_Array[1])
		time_B.setTime(Unix_Array[2])
		$("#time_A_string_X").html(gLocal.gui.noon_time+"：");
		$("#time_A_string").html(set_time_string(time_A));
		$("#time_B_string_X").html(gLocal.gui.twilight_time+"：");
		$("#time_B_string").html(set_time_string(time_B));
		$("#kala_judge").html("【"+gLocal.gui.vikala+"】");
		$("#kala_inst_1").html(gLocal.gui.no_string+gLocal.gui.eat);
		$("#kala_inst_2").html(gLocal.gui.need_inform+gLocal.gui.no_string+gLocal.gui.gama_entry);
	}
t=setTimeout('dawn_noon_display()',1000)

}
function set_time_string(date_obj){
	var hh=date_obj.getHours()
	var mm=date_obj.getMinutes()
	var ss=date_obj.getSeconds()
// add a zero in front of numbers<10
	hh=checkTime(hh)
	mm=checkTime(mm)
	ss=checkTime(ss)
	//return(hh+":"+mm+":"+ss)
	return(date_obj.toLocaleTimeString())
}
function menuLangrage(obj,year,month){
	g_language=obj.value;
	setCookie('language',g_language,365);
	window.location.assign("index.php?language="+g_language+"&y="+year+"&m="+month);

}
set_uposatha_day();
set_cur_day_era();
function set_cur_day_era(){
	if(document.getElementById('cur_day_string')!=null){
		var cur_day_string=document.getElementById('cur_day_string').innerHTML;
		var cur_date=cur_day_string.split("-")[0];
		var cur_years_diff_total=cur_day_string.split("-")[1];
		var cur_days_diff=cur_day_string.split("-")[2];
		var cur_week_day=cur_day_string.split("-")[3];
		var currLanguage=cur_day_string.split("-")[4];
		pali_date(cur_date,cur_years_diff_total,cur_days_diff,cur_week_day,currLanguage,g_coordinate_this);
	}
}
function set_uposatha_day(){
	var key_num=document.getElementById('key_num_string').innerHTML;
	var total_days=key_num.split("-")[0];
	var month_days=key_num.split("-")[1];
	var year=key_num.split("-")[2];
	for(i_uposatha=1;i_uposatha<=month_days;i_uposatha++){
		var pres_obj=pali_date_num(year,total_days-month_days+i_uposatha)[1];
		var uposatha_obj=uposatha_calculator(pres_obj.month,pres_obj.day);
		var day_id="td_"+i_uposatha;
		if(uposatha_obj.uposatha && uposatha_obj.pakkha=="kāla"){
			//document.getElementById(day_id).innerHTML+="<svg class='icon'><use xlink:href='svg/icon.svg#new_moon'></use></svg>";
			document.getElementById(day_id).innerHTML="<div class='new_moon_uposatha' >"+i_uposatha+"</div>";
		}	
		else if(uposatha_obj.uposatha && uposatha_obj.pakkha=="sukka"){
			//document.getElementById(day_id).innerHTML+="<svg class='icon'><use xlink:href='svg/icon.svg#full_moon'></use></svg>";
			document.getElementById(day_id).innerHTML="<div class='full_moon_uposatha' >"+i_uposatha+"</div>";
		}
	}


}
function pali_date_num(year,days){
	var date_num_array=new Array;
	var past_obj=new Object;
	var pres_obj=new Object;
	var left_obj=new Object;
	past_obj.year=Number(year);
	pres_obj.year=Number(year)+1;
	left_obj.year=5000-Number(year)-1;
	if(days%59<=30 && days%59!=0){//奇數月
		past_obj.month=Math.floor(days/59)*2;
		pres_obj.month=past_obj.month+1
		past_obj.day=days%59-1;
		pres_obj.day=days%59;
		pres_obj.month_length=30;
		if(pres_obj.month>=13){
			pres_obj.month=pres_obj.month%12;
			past_obj.month=pres_obj.month-1;
			past_obj.year=past_obj.year+Math.floor(pres_obj.month/12);
			pres_obj.year=past_obj.year+1;
		}
	}
	else if(days%59>30){//偶數月
		past_obj.month=Math.floor(days/59)*2+1;
		pres_obj.month=past_obj.month+1
		past_obj.day=days%59-30-1;
		pres_obj.day=past_obj.day+1
		pres_obj.month_length=29;
	}
	else if(days%59==0){//偶數月最後一天
		past_obj.month=days/59*2-1;
		pres_obj.month=past_obj.month+1
		past_obj.day=28;
		pres_obj.day=29;
		pres_obj.month_length=29;
	}
	left_obj.month=12-past_obj.month-1;
	left_obj.day=pres_obj.month_length-past_obj.day-1;
	date_num_array.push(past_obj,pres_obj,left_obj);
	return(date_num_array);
}
function get_coordinate_num(id_key){
	var LT=Number($("#LT_"+id_key+"_°")[0].value);
		LT+=Number($("#LT_"+id_key+"_’")[0].value)/60;
		LT+=Number($("#LT_"+id_key+"_”")[0].value)/3600;
		LT=Number($("#"+id_key+"_WE")[0].value+LT);
	var AT=Number($("#AT_"+id_key+"_°")[0].value);
		AT+=Number($("#AT_"+id_key+"_’")[0].value)/60;
		AT+=Number($("#AT_"+id_key+"_”")[0].value)/3600;
		AT=Number($("#"+id_key+"_NS")[0].value+AT);
	var coordinate_obj=new Object;
	coordinate_obj.LT=LT;
	coordinate_obj.AT=AT;
	return(coordinate_obj);

}

function pali_date(id,m_days,year,days,week_day,currLanguage,position){
//改參數
	$('#cur_day_string').html(id+"-"+m_days+"-"+year+"-"+days+"-"+week_day+"-"+currLanguage);
var coordinate=new Object;
coordinate=g_coordinate_this;
//var month=$("#sun_AT")[0].innerText.split('-')[0];
var day_selected=$("#sun_AT")[0].innerText.split('-')[1];
//選定日期
var date_select=$("#key_num_string")[0].innerText.split('-')[2]+"-";
	date_select+=$("#key_num_string")[0].innerText.split('-')[3]+"-";
	date_select+=id;
var year_str=$("#key_num_string")[0].innerText.split('-')[2];
var month_str=$("#key_num_string")[0].innerText.split('-')[3];
//var date_select=$("#date_picker")[0].value;
		$("#time_string").load("calendar_data.php?atitude="+coordinate.AT+"&longitude="+coordinate.LT+"&date="+date_select);

//改變日曆中選中日的樣式
	for(var i_date=1;i_date<=m_days;i_date++){
		$("#td_"+i_date)[0].style="";
	}
	$("#td_"+id)[0].style="background:purple;";
//佛曆數據解析
	var date_num_array=pali_date_num(year,days);
	var past=date_num_array[0];
	var pres=date_num_array[1];
	var left=date_num_array[2];
//生成佛曆圖表外殻
	var pres_date_string ="<span>"+gLocal.gui.BE+"</span>";
		pres_date_string+="<span style='font-size: 200%;'>";
		pres_date_string+=pres.year+gLocal.gui.year_1;
		pres_date_string+=pres.month+gLocal.gui.month_1;
		pres_date_string+=pres.day+gLocal.gui.day+"</span>";
		pres_date_string+="<span id='BE_icon'></span>";

	$("#pali_era_graph").html(pres_date_string);
//生成佛曆巴利外殻
	var past_date_string_pali ="<span id='past_string'></span>";
	var pres_date_string_pali ="<span id='pres_string'></span>";
	var left_date_string_pali ="<span id='left_string'></span>";
	var output_string_pali = past_date_string_pali+left_date_string_pali+pres_date_string_pali;
	$("#pali_era_pali").html(output_string_pali);
//寫入佛曆圖表數據
	var pres_language_string_0= "";
		pres_language_string_0+=get_year_name(pres.year).string_0;
		pres_language_string_0+=get_month_name(pres.month,pres.day).string_0;
		pres_language_string_0+=get_week_day_name(week_day).string_0;

	var past_date_string ="<div>"+gLocal.gui.dhamma_time+"</div>"
		past_date_string+="<div><span class='BE_icon_span'>"+gLocal.gui.past+"：</span>";
		past_date_string+=past.year+gLocal.gui.years;
		if(past.month!=0){
			past_date_string+=past.month+gLocal.gui.months;
		}
		if(!(past.month==0 && past.day==0)){
			past_date_string+=gLocal.gui.and_another;
		}
		if(past.day!=0){
			past_date_string+=past.day+gLocal.gui.days;
		}
		past_date_string+="</div>";

	var left_date_string= "<div><span class='BE_icon_span'>"+gLocal.gui.left+"：</span>";
		left_date_string+=left.year+gLocal.gui.years;
		if(left.month!=0){
			left_date_string+=left.month+gLocal.gui.months;
		}
		if(!(left.month==0 && left.day==0)){
			left_date_string+=gLocal.gui.and_another;
		}
		if(left.day!=0){
			left_date_string+=left.day+gLocal.gui.days;
		}
		left_date_string+="</div>";

		$('#BE_icon').html(pres_language_string_0+"<br>"+past_date_string+left_date_string);





//解析佛曆數據為文字
	var past_language_string="";
	var pres_language_string="";
	var left_language_string="";
	var pali_begin="idāni kho pana ";
	var pali_past_end=" atikkantāni.";
	var pali_left_end=" avasiṭṭhāni.";
	if(past.day==1 && past.month!=0){
		past_language_string+=pali_begin;
		past_language_string+=get_year_pali_string(past.year)+"ceva, ";
		past_language_string+=get_day_pali_string(past.day).pre;
		past_language_string+=get_month_pali_string(past.month).suff
		past_language_string+=pali_past_end;
	}
	else if(past.day==1 && past.month==0){
		past_language_string+=pali_begin;
		past_language_string+=get_day_pali_string(past.day).pre;
		past_language_string+=get_year_pali_string(past.year);
		past_language_string+=get_month_pali_string(past.month).suff
		past_language_string+=pali_past_end;
	}
	else if(past.day==0 && past.month==1){
		past_language_string+=pali_begin;
		past_language_string+=get_day_pali_string(past.month).pre;
		past_language_string+=get_year_pali_string(past.year)+"ceva, "
		past_language_string+=get_month_pali_string(past.day).suff
		past_language_string+=pali_past_end;
	}
	else if(past.day==0 && past.month>1){
		past_language_string+=pali_begin;
		past_language_string+=get_year_pali_string(past.year)
		past_language_string+=get_month_pali_string(past.month).suff;
		past_language_string+=get_day_pali_string(past.day).suff;
		past_language_string+=pali_past_end;
	}

	else{
		past_language_string+=pali_begin;
		past_language_string+=get_year_pali_string(past.year)
		past_language_string+=get_month_pali_string(past.month).suff+"ca, ";
		past_language_string+=get_day_pali_string(past.day).suff;
		past_language_string+=pali_past_end;
	}
	if(left.day==1 && left.month!=0){
		left_language_string+=get_year_pali_string(left.year)+"ceva, ";
		left_language_string+=get_day_pali_string(left.day).pre;
		left_language_string+=get_month_pali_string(left.month).suff;
		left_language_string+=pali_left_end;
	}
	else if(left.day==1 && left.month==0){
		left_language_string+=get_day_pali_string(left.day).pre;
		left_language_string+=get_year_pali_string(left.year);
		left_language_string+=get_month_pali_string(left.month).suff
		left_language_string+=pali_left_end;
	}
	else if(left.day==0 && left.month==1){
		left_language_string+=get_day_pali_string(left.month).pre;
		left_language_string+=get_year_pali_string(left.year)+"ceva, ";
		left_language_string+=get_month_pali_string(left.day).suff
		left_language_string+=pali_left_end;
	}
	else if(left.day==0 && left.month>1){
		left_language_string+=get_year_pali_string(left.year);
		left_language_string+=get_month_pali_string(left.month).suff;
		left_language_string+=get_day_pali_string(left.day).suff;
		left_language_string+=pali_left_end;
	}
	else{
		left_language_string+=get_year_pali_string(left.year);
		left_language_string+=get_month_pali_string(left.month).suff+"ca, ";
		left_language_string+=get_day_pali_string(left.day).suff;
		left_language_string+=pali_left_end;

	}

		pres_language_string+=get_year_name(pres.year).string_1;
		pres_language_string+=get_month_name(pres.month,pres.day).string_1;
		pres_language_string+=get_week_day_name(week_day).string_1;

	switch(currLanguage){
	case "si":
		for(i_sinhala in char_unicode_to_si_n){
			eval("past_language_string=past_language_string.replace(/"+char_unicode_to_si_n[i_sinhala].id+"/g,char_unicode_to_si_n[i_sinhala].value);");
			eval("pres_language_string=pres_language_string.replace(/"+char_unicode_to_si_n[i_sinhala].id+"/g,char_unicode_to_si_n[i_sinhala].value);");
			eval("left_language_string=left_language_string.replace(/"+char_unicode_to_si_n[i_sinhala].id+"/g,char_unicode_to_si_n[i_sinhala].value);");
		}
	break;
	case "my":
		for(r_to_m_i in char_roman_to_myn){
			eval("past_language_string=past_language_string.replace(/"+char_roman_to_myn[r_to_m_i].id+"/g,char_roman_to_myn[r_to_m_i].value);");
			eval("pres_language_string=pres_language_string.replace(/"+char_roman_to_myn[r_to_m_i].id+"/g,char_roman_to_myn[r_to_m_i].value);");
			eval("left_language_string=left_language_string.replace(/"+char_roman_to_myn[r_to_m_i].id+"/g,char_roman_to_myn[r_to_m_i].value);");
		}
	break;
	default:
		past_language_string=past_language_string.charAt(0).toUpperCase()+past_language_string.slice(1);
		left_language_string=left_language_string.charAt(0).toUpperCase()+left_language_string.slice(1);
		//pres_language_string=pres_language_string;
		var new_string=pres_language_string.charAt(0).toUpperCase();
		new_string+=pres_language_string.split('\. ')[0].slice(1)+". ";
		new_string+=pres_language_string.split('\. ')[1].charAt(0).toUpperCase();
		new_string+=pres_language_string.split('\. ')[1].slice(1);
		pres_language_string=new_string;
	}
	$('#past_string').html(past_language_string);
	$('#pres_string').html(pres_language_string);
	$('#left_string').html(left_language_string);

}
function get_year_pali_string(year){
	if(year>999){
		year=year.toString()
		Tp=year.charAt(0)*1000;
		Hp=year.charAt(1)*100;
		Sp=year.slice(2,4);
	}
		for(i_year in pali_num_str){
			if(Tp==pali_num_str[i_year].id){
				var Tp_string=pali_num_str[i_year].value;
			}
			if(Hp==pali_num_str[i_year].id){
				var Hp_string=pali_num_str[i_year].value;
			}
			if(Sp==pali_num_str[i_year].id){
				var Sp_string=pali_num_str[i_year].value;
		}
	}
	if(year==1){
		var year_pali_end=" saṃvaccharaṃ ceva, ";
	}
	else{
		var year_pali_end=" saṃvaccharāni ";
	}
	var year_string=Tp_string+"-"+Hp_string+"-"+Sp_string+year_pali_end;
	return(year_string);
}
function get_month_pali_string(month){
	var month_obj=new Object;
if(month>=2){
	month=month.toString();
	for(i_month in pali_num_str_pl){
		if(month==pali_num_str_pl[i_month].id){
			var month_num_string=pali_num_str_pl[i_month].value;
		}
	}
	month_obj.suff=month_num_string+" māsāni "
	month_obj.pre="";
}
else if(month==1){
	month_obj.suff="dve-pakkhāni";
	month_obj.pre="eka-māsādhika-";
}
else if(month==0){
	month_obj.suff="";
	month_obj.pre="";
}
return(month_obj);

}
function get_day_pali_string(day){
	var day_obj=new Object;
if(day>=2){
	day=day.toString();
	for(i_day in pali_num_str_pl){
		if(day==pali_num_str_pl[i_day].id){
			var day_num_string=pali_num_str_pl[i_day].value;
		}
	}
	day_obj.suff=day_num_string+" divasāni "
	day_obj.pre="";
}
else if(day==1){
	day_obj.suff="";
	day_obj.pre="eka-divasādhika-";
}
else if(day==0){
	day_obj.suff="";
	day_obj.pre="";
}
return(day_obj);
}
function get_year_name(year){
	var year_num=year%12;
	var year_name_string=new Object;
	for(i_year_name in pali_year_name){
		if(year_num==pali_year_name[i_year_name].id){
			year_name_string.value=pali_year_name[i_year_name].value;
			year_name_string.icon=pali_year_name[i_year_name].icon;
		}
	}
	year_name_string.string_0 ="<div><span class='BE_icon_span'>"+gLocal.gui.year_0+"</span>";
	year_name_string.string_0+=year_name_string.icon+"</div>";
	year_name_string.string_1="ayaṃ "+year_name_string.value+"-saṃvacchare ";
	return(year_name_string);
}
function uposatha_calculator(month,day){
	var day_obj=new Object;

	if(month%2==1){
		var days_per_month=30;
	}
	else{
		var days_per_month=29;
	}
	if(days_per_month==30){
		if(day<=15){
			day_obj.day=day;
			day_obj.pakkha="kāla "+gLocal.gui.kala_pakkha;
			day_obj.pakkha_icon="🌖→🌑";
			if(day==15){
				day_obj.uposatha=true;
			}
			else{
				day_obj.uposatha=false;
			}
		}
		else{
			day_obj.day=day-15;
			day_obj.pakkha="sukka";//🌕🌗🌗🌒🌓🌔🌝🌚🌞🌜🌛
			day_obj.pakkha_icon="🌒→🌕";
			if(day==30){
				day_obj.uposatha=true;
			}
			else{
				day_obj.uposatha=false;
			}
		}
	}
	else if(days_per_month==29){
		if(day<=14){
			day_obj.day=day;
			day_obj.pakkha="kāla";
			day_obj.pakkha_icon="🌖→🌑";
			if(day==14){
				day_obj.uposatha=true;
			}
			else{
				day_obj.uposatha=false;
			}
		}
		else{
			day_obj.day=day-14;
			day_obj.pakkha="sukka";
			day_obj.pakkha_icon="🌒→🌕";
			if(day==29){
				day_obj.uposatha=true;
			}
			else{
				day_obj.uposatha=false;
			}
		}
	}
	return(day_obj);
}
function get_month_name(month,day){
	var return_string=new Object;
	for(i_month_name in pali_month_name){
		if(month==pali_month_name[i_month_name].id){
			var month_name_string=pali_month_name[i_month_name].value;
			var season_name_string=pali_month_name[i_month_name].season;
			var season_icon_string=pali_month_name[i_month_name].season_icon;
		}
	}
	return_string.string_0 ="<div><span class='BE_icon_span'>"+gLocal.gui.season+"</span>";
	return_string.string_0+=season_icon_string+"</div>";
	return_string.string_0+="<div><span class='BE_icon_span'>"+gLocal.gui.month+"</span>";
	return_string.string_0+=month_name_string+"</div>";
	return_string.string_1=season_name_string+"-utu. "
	return_string.string_1+="asmiṃ utumhi "+month_name_string+"-māsassa ";
	var day_object=uposatha_calculator(month,day);
	for(i_day_name in pali_date_name){
		if(day_object.day==pali_date_name[i_day_name].id){
			day_name_string=pali_date_name[i_day_name].value;
			var day_num_string=day_object.day
		}
	}
	var pakkha_name_string=day_object.pakkha+"-pakkhe "+day_name_string;
	return_string.string_0+="<div><span class='BE_icon_span'>"+gLocal.gui.pakkha+"</span>";
	return_string.string_0+=day_object.pakkha_icon+"</div>";

	return_string.string_0+="<div><span class='BE_icon_span'>"+gLocal.gui.date+"</span>";
	return_string.string_0+=day_num_string+"</div>";

	return_string.string_1+=pakkha_name_string+", ";
	return(return_string);
}
function get_week_day_name(week_day){
	var week_day_string=new Object;
	for(i_week in pali_week_day_name){
		if(week_day==pali_week_day_name[i_week].id){
			week_day_string.string_0=pali_week_day_name[i_week].value
			week_day_string.string_1=pali_week_day_name[i_week].value
		}
	}
	week_day_string.string_0="<div><span class='BE_icon_span'>"+gLocal.gui.week_day+"</span>"+week_day_string.string_0+"</div>";
	week_day_string.string_1+="-varamidan’ti daṭṭhabbaṃ."
	return(week_day_string);
}
function set_display(hide_id,show_id){
	$("#"+show_id).show();
	$("#"+hide_id).hide();
}
function confirm_position(hide_id,show_id,position){
	$("#"+show_id).show();
	$("#"+hide_id).hide();
	var LT_str="";
	if($("#LT_"+position+"_°")[0].value!=0){
		LT_str+=$("#LT_"+position+"_°")[0].value+"°";
	}
	if($("#LT_"+position+"_’")[0].value!=0){
		LT_str+=$("#LT_"+position+"_’")[0].value+"’";
	}
	if($("#LT_"+position+"_”")[0].value!=0){
		LT_str+=$("#LT_"+position+"_”")[0].value+"”";
	}
	if(LT_str!=""){
		if($("#"+position+"_WE")[0].value=="+"){
			LT_str+="E";
		}
		else{
			LT_str+="W";
		}
	}

	var AT_str="";
	if($("#AT_"+position+"_°")[0].value!=0){
		AT_str+=$("#AT_"+position+"_°")[0].value+"°";
	}
	if($("#AT_"+position+"_’")[0].value!=0){
		AT_str+=$("#AT_"+position+"_’")[0].value+"’";
	}
	if($("#AT_"+position+"_”")[0].value!=0){
		AT_str+=$("#AT_"+position+"_”")[0].value+"”";
	}
	if(AT_str!=""){
		if($("#"+position+"_NS")[0].value=="+"){
			AT_str+="N";
		}
		else{
			AT_str+="S";
		}
	}
		$("#"+position+"_position_string").html(LT_str+" "+AT_str);
}
function getLocation(){//自动定位
	if(navigator.geolocation){
		navigator.geolocation.getCurrentPosition(showPosition,showError);
    }
	else{
		$("#selected_position_string").html("Geolocation is not supported by this browser.");
	}
}
function showPosition(position){
	alert("get cordinate success");
	g_coordinate_this.AT=position.coords.latitude;
	g_coordinate_this.LT=position.coords.longitude;
	if(g_coordinate_this.AT>=0){
		var AT_string=angle_trans(g_coordinate_this.AT)+"N";
	}
	else{
		var AT_string=angle_trans(g_coordinate_this.AT)+"S";
	}
	if(g_coordinate_this.LT>=0){
		var LT_string=angle_trans(g_coordinate_this.LT)+"E";
	}
	else{
		var LT_string=angle_trans(g_coordinate_this.LT)+"W";
	}
	$("#selected_position_string").html(AT_string+" "+LT_string);

}
function showError(error){
  switch(error.code) {
    case error.PERMISSION_DENIED:
      alert("定位失败,用户拒绝请求地理定位");
      break;
    case error.POSITION_UNAVAILABLE:
      alert("定位失败,位置信息是不可用");
      break;
    case error.TIMEOUT:
      alert("定位失败,请求获取用户位置超时");
      break;
    case error.UNKNOWN_ERROR:
      alert("定位失败,定位系统失效");
      break;
	}
}
 function confirm(){
	confirm_position("position_input","position_change","selected")
	var select_day_string=new Array();
	select_day_string=$('#cur_day_string')[0].innerText.split('-');
		pali_date(select_day_string[0],select_day_string[1],select_day_string[2],select_day_string[3],select_day_string[4],select_day_string[5],g_coordinate_this)
}
dawn_noon_display();
function air_confirm(place){
	confirm_position(place+"_position_input",place+"_position_result",place);
	var coordinate= get_coordinate_num(place);
	var air_date=$("#"+place+"_date")[0].value;
	var air_time=$("#"+place+"_time")[0].value;
	$("#"+place+"_position_string")[0].innerText+=" "+air_date+" "+air_time;

	$("#air_time_string").load("calendar_data.php?atitude="+coordinate.AT+"&longitude="+coordinate.LT+"&date="+air_date);
	var air_noon_time=new Date();
		air_noon_time.setTime($("#air_time_string")[0].innerText.split('-')[1]);
	$("#"+place+"_position_string")[0].innerText+=gLocal.gui.noon_time+set_time_string(air_noon_time);
}



function computeSunRiseSunSet(Latitude, Longitude, TimeZone) {
    var curTime = new Date();
    // Variable names used: B5, C, C2, C3, CD, D, DR, H, HR, HS, L0, L5, M, MR, MS, N, PI, R1, RD, S1, SC, SD, str
    var retVal = new Object();
    var PI = Math.PI;
    var DR = PI / 180;
    var RD = 1 / DR;
    var B5 = Latitude;
    var L5 = Longitude;
    var H = -1 * (curTime.getTimezoneOffset() / 60 * -1); // Local timezone
    // Overriding TimeZone to standardize on UTC
    // H = 0;
    var M = curTime.getMonth() + 1;
    var D = curTime.getDate();
    B5 = DR * B5;
    var N = parseInt(275 * M / 9) - 2 * parseInt((M + 9) / 12) + D - 30;
    var L0 = 4.8771 + .0172 * (N + .5 - L5 / 360);
    var C = .03342 * Math.sin(L0 + 1.345);
    var C2 = RD * (Math.atan(Math.tan(L0 + C)) - Math.atan(.9175 * Math.tan(L0 + C)) - C);
    var SD = .3978 * Math.sin(L0 + C);
    var CD = Math.sqrt(1 - SD * SD);
    var SC = (SD * Math.sin(B5) + .0145) / (Math.cos(B5) * CD);
    if (Math.abs(SC) <= 1) {
        var C3 = RD * Math.atan(SC / Math.sqrt(1 - SC * SC));
        var R1 = 6 - H - (L5 + C2 + C3) / 15;
        var HR = parseInt(R1);
        var MR = parseInt((R1 - HR) * 60);
        retVal.SunRise = parseTime(HR + ":" + MR);
        var TargetTimezoneOffset = (TimeZone * 60 * 60 * 1000) + (retVal.SunRise.getTimezoneOffset() * 60 * 1000);
        var transformedSunRise = new Date(retVal.SunRise.getTime() + TargetTimezoneOffset);
        var strSunRise = "日出" + transformedSunRise.getHours() + ":" + (transformedSunRise.getMinutes() < 10 ? "0" + transformedSunRise.getMinutes() : transformedSunRise.getMinutes());
        var S1 = 18 - H - (L5 + C2 - C3) / 15;
        var HS = parseInt(S1);
        var MS = parseInt((S1 - HS) * 60);
        retVal.SunSet = parseTime(HS + ":" + MS);
        var transformedSunSet = new Date(retVal.SunSet.getTime() + TargetTimezoneOffset);
        var strSunSet = "日落" + transformedSunSet.getHours() + ":" + (transformedSunSet.getMinutes() < 10 ? "0" + transformedSunSet.getMinutes() : transformedSunSet.getMinutes());
        retVal.Noon = new Date((retVal.SunRise.getTime() + retVal.SunSet.getTime()) / 2);
        var transformedNoon = new Date(retVal.Noon.getTime() + TargetTimezoneOffset);
        var strNoon = "正午" + transformedNoon.getHours() + ":" + (transformedNoon.getMinutes() < 10 ? "0" + transformedNoon.getMinutes() : transformedNoon.getMinutes());
    }
    else {
        if (SC > 1) {
            // str="Sun up all day";
            strSunRise = ".";
            strNoon = ".";
            strSunSet = ".";
            var tDate = new Date();
            // Set Sunset to be in the future ...
            retVal.SunSet = new Date(tDate.getFullYear() + 1, tDate.getMonth(), tDate.getDay(), tDate.getHours());
            // Set Sunrise to be in the past ...
            retVal.SunRise = new Date(tDate.getFullYear() - 1, tDate.getMonth(), tDate.getDay(), tDate.getHours() - 1);
        }
        if (SC < -1) {
            // str="Sun down all day";
            strSunRise = ".";
            strNoon = ".";
            strSunSet = ".";
            // Set Sunrise and Sunset to be in the future ...
            retVal.SunRise = new Date(tDate.getFullYear() + 1, tDate.getMonth(), tDate.getDay(), tDate.getHours());
            retVal.SunSet = new Date(tDate.getFullYear() + 1, tDate.getMonth(), tDate.getDay(), tDate.getHours());
        }
    }
    retVal.strSunRise = strSunRise;
    retVal.strNoon = strNoon;
    retVal.strSunSet = strSunSet;
    retVal.str = strSunRise + ' | ' + strNoon + ' | ' + strSunSet;
    return retVal;
}
////////////////////////////////////////////////////////////////////////////////
//
// parseTime(string aTime) - takes a string of time in the format HH:MM:SS
//                           and returns Javascript Date Object
//
////////////////////////////////////////////////////////////////////////////////
function parseTime(aTime) {
    var aDateTimeObject = 'none';
    if (aTime !== undefined && aTime.length) {
        aDateTimeObject = GMTTime();
        try {
            var theHour = parseInt(aTime.split(':')[0]);
            var theMinutes = parseInt(aTime.split(':')[1]);
            aDateTimeObject.setHours(theHour);
            aDateTimeObject.setMinutes(theMinutes);
        }
        catch (ex) {
        }
    }
    return aDateTimeObject;
}
////////////////////////////////////////////////////////////////////////////////
//
// GMTTime() - returns time adjusted to GMT (Universal Time)
//
////////////////////////////////////////////////////////////////////////////////
function GMTTime() {
    var aDate = new Date();
    var aDateAdjustedToGMTInMS = aDate.getTime() + (aDate.getTimezoneOffset() * 60 * 1000);
    return (new Date(aDateAdjustedToGMTInMS));
}

</script>
