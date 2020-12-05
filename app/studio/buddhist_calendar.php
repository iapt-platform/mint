<?php
require 'checklogin.inc';
require 'config.php';

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
if(file_exists($dir_language.$currLanguage.".php")){
	require $dir_language.$currLanguage.".php";
}
else{
	include $dir_language."default.php";
}

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



?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>
<head>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8"/>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<link type="text/css" rel="stylesheet" href="css/style.css"/>
	<link type="text/css" rel="stylesheet" href="css/color_day.css" id="colorchange" />
	<link type="text/css" rel="stylesheet" href="css/style_mobile.css" media="screen and (max-width:800px)">
	<title>PCD Studio</title>
	<script language="javascript" src="config.js"></script>
	<script language="javascript" src="js/common.js"></script>
	<script language="javascript" src="charcode/sinhala.js"></script>
	<script language="javascript" src="charcode/myanmar.js"></script>
	<script language="javascript" src="charcode/unicode.js"></script>
	<link type="text/css" rel="stylesheet" href="css/style.css"/>

	<script language="javascript" src="module/editor/language/default.js"></script>	
	<script language="javascript" src="module/editor/language/<?php echo $currLanguage; ?>.js"></script>
	
	<script src="js/jquery-3.3.1.min.js"></script>
	<script language="javascript" src="module/editor_palicannon/palicannon.js"></script>
	<script language="javascript" src="module/editor_palicannon/language/<?php echo $currLanguage; ?>.js"></script>
<style type="text/css">
.BE_icon_span{
	width: 10em;
    display: inline-block;
}
#BE_icon{
	font-size: 150%;
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
	font-size: 150%;
	width: 34em;
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

</style>	

	<!--加载语言文件 -->
	<script language="javascript" src="language/default.js"></script>
	<script language="javascript">
	<?php 
	//加载js语言包
	require_once '../public/load_lang_js.php';
	?>
	</script>	

	//加载js语言包


	<!--加载语言文件结束 -->
	<script src="js/jquery-3.3.1.min.js"></script>
	<script src="js/fixedsticky.js"></script>
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
			<div id="index_nav">
				<button><a href="index.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1018'];?></a></button>
				<button><a href="index_pc.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor_wizard']['1002'];?></a></button>
				<button><a href="filenew.php?language=<?php echo $currLanguage; ?>"><?php echo $module_gui_str['editor']['1064'];?></a></button>
				<button class="selected"><?php echo $module_gui_str['editor']['1052'];?></button>
				
			
			</div>
			<div class="toolgroup1">
				
				<span><?php echo $module_gui_str['editor']['1050'];?></span>
	<?php
		echo "<select id='id_language' name='menu' onchange=\"menuLangrage(this,".$year.",".$month.")\">";
		echo "<option value='pali' >Pāḷi Roman</option>";
		echo "<option value='en' >English</option>";
		echo "<option value='sinhala' >සිංහල</option>";
		echo "<option value='myanmar' >myanmar</option>";
		echo "<option value='zh' >简体中文</option>";
		echo "<option value='tw' >繁體中文</option>";
		echo "</select>";
		echo $_local->gui->welcome;
		echo "<a href=\"setting.php?item=account\">";
		echo $_COOKIE["nickname"];
		echo "</a>";
		echo $_local->gui->to_the_dhamma;
		echo "<a href='login.php?op=logout'>";
		echo $_local->gui->logout;
		echo "</a>";
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
echo "<div style='display:flex;'>";
echo "<center style='display: flex;flex: 3;flex-direction: column;padding: 40px;'>";
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
	$year_text="{$year}".$module_gui_str['tools']['1018'];//年
}
//生成月份名稱
if($currLanguage=="en"){
	$month_text=date('F',strtotime("{$year}-{$month}-1"));
}
else{
	$month_text="{$month}".$module_gui_str['tools']['1019'];//月
}
//输出表头
echo "<h2 style='font-size: 150%;'>";
echo "<a href='buddhist_calendar.php?y={$prevyear}&m={$prevmonth}'>";
echo $module_gui_str['tools']['1027'];//上一月
echo "</a>|";
echo $year_text;
echo $month_text;
echo "|<a href='buddhist_calendar.php?y={$nextyear}&m={$nextmonth}'>";
echo $module_gui_str['tools']['1028'];//下一月
echo "</a></h2>";


//输出日期表格
echo "<table class='table_body' >";
echo "<tr class='table_line'>";
echo "<th class='table_column'>".$module_gui_str['tools']['1026']."</th>";
echo "<th class='table_column'>".$module_gui_str['tools']['1020']."</th>";
echo "<th class='table_column'>".$module_gui_str['tools']['1021']."</th>";
echo "<th class='table_column'>".$module_gui_str['tools']['1022']."</th>";
echo "<th class='table_column'>".$module_gui_str['tools']['1023']."</th>";
echo "<th class='table_column'>".$module_gui_str['tools']['1024']."</th>";
echo "<th class='table_column'>".$module_gui_str['tools']['1025']."</th>";
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
			echo "<td class='table_column' id='td_{$i}' onclick=\"pali_date($i,$days,$years_diff_total,$days_diff_X,$j,'".$currLanguage."')\")' ><div class='td_today'>{$i}</div>";
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
			echo "<td class='table_column' id='td_{$i}' onclick=\"pali_date($i,$days,$years_diff_total,$days_diff,$j,'".$currLanguage."')\">{$i}</td>";
			if($i==$days){
				$key_days_num=$days_diff;
			}
        }
        else{
        	$days_diff_X=$days_diff-$selected_date+$i;
        	$cur_years_diff_total=$years_diff_total;
        	$cur_days_diff=$days_diff_X;
        	$cur_week_day=$j;
			echo "<td class='table_column' id='td_{$i}' onclick=\"pali_date($i,$days,$years_diff_total,$days_diff_X,$j,'".$currLanguage."')\">{$i}</td>";
			if($i==$days){
				$key_days_num=$days_diff_X;
			}
        }
        $i++;
    }
    echo "</tr>";
}
echo "</table>";

echo "<div style='display:flex; margin: 20px;'>";
echo "<div style='display:grid; flex:3'>";
echo "<span id='time_A_string_X' style='font-size: 150%;'></span>";
echo "<span id='clock_string_X' style='font-size: 150%;'></span>";
echo "<span id='time_B_string_X' style='font-size: 150%; '></span>";
echo "</div>";
echo "<div style='display:grid; flex:2'>";
echo "<span id='time_A_string' style='font-size: 150%; '></span>";
echo "<span id='clock_string' style='font-size: 150%; '></span>";
echo "<span id='time_B_string' style='font-size: 150%; '></span>";
echo "</div>";
echo "<div style='display:grid; flex:6'>";
echo "<span id='kala_judge' style='font-size: 150%; '></span>";
echo "<span id='kala_inst_1' style='font-size: 150%; '></span>";
echo "<span id='kala_inst_2' style='font-size: 150%; '></span>";
echo "</div>";
echo "</div>";

echo "<span id='key_num_string' style='display: none; '>{$key_days_num}-{$days}-{$year}</span>";
if($cur_years_diff_total!=null && $cur_days_diff!=null && $cur_week_day!=null){
echo "<span id='cur_day_string' style='display: none; '>{$cur_date}-{$days}-{$cur_years_diff_total}-{$cur_days_diff}-{$cur_week_day}-{$currLanguage}</span>";
}
echo "</center>";



date_default_timezone_set("Asia/Rangoon");
$Unix_now_time=strtotime("now");
$sun_info=date_sun_info(strtotime("now"),14.150142,98.226393);//7.738562,80.519675
$sun_info_1=date_sun_info(strtotime("+1 day"),7.738562,80.519675);
$Unix_dawn=$sun_info['civil_twilight_begin']*1000;
$Unix_dawn_1=$sun_info_1['civil_twilight_begin']*1000;
$Unix_noon=($sun_info['sunrise']+$sun_info['sunset'])/2*1000;
$Unix_noon_1=($sun_info_1['sunrise']+$sun_info_1['sunset'])/2*1000;
echo "<span id='time_string' style='display: none; '>{$Unix_dawn}-{$Unix_noon}-{$Unix_dawn_1}-{$Unix_noon_1}</span>";

//echo date("H:i:s",$Unix_dawn)."<br/>";
//echo date("H:i:s",$Unix_noon)."<br/>";
//太陽赤緯
$diff_time=strtotime("now")-date_timestamp_get(date_create("{$year}-01-1"));
$N=$diff_time/1000/3600/24;
$year_day=366-ceil($year%4/4);
$b=2*pi()*($N-1)/$year_day;
$sun_angle=0.006918-0.399912*cos($b)+0.070257*sin($b)-0.006758*cos($b*2)+0.000907*sin($b*2)-0.002697*cos($b*3)+0.00148*sin($b*3);

//echo "<button onclick='set_uposatha_day()'>set uposatha</button>";
echo "<div id='pali_era' style='font-size: 150%;width: 40em;display: flex;flex: 7;padding: 40px;flex-direction: column;'>";
echo "<div id='pali_era_graph' style='display:grid;flex:3;'></div>";
echo "<div id='pali_era_pali' style='display:grid;flex:7;'></div>";
echo "</div>";
//echo date_format($vesakha_day,"Y-m-d")."|".date_format($vesakha_day_1,"Y-m-d")
echo "</div>";


?>
<script language="javascript">
dawn_noon_display();
function dawn_noon_display(){
	var Unix_string=document.getElementById('time_string').innerHTML
	var Unix_Array=Unix_string.split("-");
	var time_A=new Date();
	var time_B=new Date();
	$("#clock_string_X").html(local_gui.now_time+"：")
	if(g_Unix_now<Unix_Array[0]){
		time_B.setTime(Unix_Array[0])
		$("#time_B_string_X").html(local_gui.twilight_time+"：");
		$("#time_B_string").html(set_time_string(time_B));
		$("#kala_judge").html("【"+local_gui.vikala+"】");
		$("#kala_inst_1").html(local_gui.no_string+local_gui.eat);
		$("#kala_inst_2").html(local_gui.need_inform+local_gui.no_string+local_gui.gama_entry);

	}
	else if(g_Unix_now>=Unix_Array[0] && g_Unix_now<Unix_Array[1]){
		time_A.setTime(Unix_Array[0])
		time_B.setTime(Unix_Array[1])
		$("#time_A_string_X").html(local_gui.twilight_time+"：");
		$("#time_A_string").html(set_time_string(time_A));
		$("#time_B_string_X").html(local_gui.noon_time+"：");
		$("#time_B_string").html(set_time_string(time_B));
		$("#kala_judge").html("【"+local_gui.kala+"】");
		$("#kala_inst_1").html(local_gui.yes_string+local_gui.eat);
		$("#kala_inst_2").html(local_gui.need_inform+local_gui.yes_string+local_gui.gama_entry);
	}
	else if(g_Unix_now>=Unix_Array[1] && g_Unix_now<Unix_Array[2]){
		time_A.setTime(Unix_Array[1])
		time_B.setTime(Unix_Array[2])
		$("#time_A_string_X").html(local_gui.noon_time+"：");
		$("#time_A_string").html(set_time_string(time_A));
		$("#time_B_string_X").html(local_gui.twilight_time+"：");
		$("#time_B_string").html(set_time_string(time_B));
		$("#kala_judge").html("【"+local_gui.vikala+"】");
		$("#kala_inst_1").html(local_gui.no_string+local_gui.eat);
		$("#kala_inst_2").html(local_gui.need_inform+local_gui.no_string+local_gui.gama_entry);
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
	return(hh+":"+mm+":"+ss)
}
function menuLangrage(obj,year,month){
	g_language=obj.value;
	setCookie('language',g_language,365);
	window.location.assign("buddhist_calendar.php?language="+g_language+"&y="+year+"&m="+month);
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
		pali_date(cur_date,cur_years_diff_total,cur_days_diff,cur_week_day,currLanguage);
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
function pali_date(id,m_days,year,days,week_day,currLanguage){
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
	var pres_date_string ="<span>"+local_gui.today+local_gui.BE+"</span>";
		pres_date_string+="<span style='font-size: 400%;'>";
		pres_date_string+=pres.year+local_gui.year_1;
		pres_date_string+=pres.month+local_gui.month_1;
		pres_date_string+=pres.day+local_gui.day+"</span>";
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

	var past_date_string ="<div>"+local_gui.dhamma_time+"</div>"
		past_date_string+="<div><span class='BE_icon_span'>"+local_gui.past+"：</span>";
		past_date_string+=past.year+local_gui.years;
		past_date_string+=past.month+local_gui.months;
		past_date_string+=past.day+local_gui.days+"</div>";

	var left_date_string= "<div><span class='BE_icon_span'>"+local_gui.left+"：</span>";
		left_date_string+=left.year+local_gui.years
		left_date_string+=left.month+local_gui.months
		left_date_string+=left.day+local_gui.days+"</div>";

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
	case "sinhala":
		for(i_sinhala in char_unicode_to_si_n){
			eval("past_language_string=past_language_string.replace(/"+char_unicode_to_si_n[i_sinhala].id+"/g,char_unicode_to_si_n[i_sinhala].value);");
			eval("pres_language_string=pres_language_string.replace(/"+char_unicode_to_si_n[i_sinhala].id+"/g,char_unicode_to_si_n[i_sinhala].value);");
			eval("left_language_string=left_language_string.replace(/"+char_unicode_to_si_n[i_sinhala].id+"/g,char_unicode_to_si_n[i_sinhala].value);");
		}
	break;
	case "myanmar":
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
	year_name_string.string_0 ="<div><span class='BE_icon_span'>"+local_gui.year_0+"</span>";
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
			day_obj.pakkha="kāla";
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
	return_string.string_0 ="<div><span class='BE_icon_span'>"+local_gui.season+"</span>";
	return_string.string_0+=season_icon_string+"</div>";
	return_string.string_0+="<div><span class='BE_icon_span'>"+local_gui.month+"</span>";
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
	return_string.string_0+="<div><span class='BE_icon_span'>"+local_gui.pakkha+"</span>";
	return_string.string_0+=day_object.pakkha_icon+"</div>";

	return_string.string_0+="<div><span class='BE_icon_span'>"+local_gui.date+"</span>";
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
	week_day_string.string_0="<div><span class='BE_icon_span'>"+local_gui.week_day+"</span>"+week_day_string.string_0+"</div>";
	week_day_string.string_1+="-varamidan’ti daṭṭhabbaṃ."
	return(week_day_string);
}

</script>
