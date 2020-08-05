<?php
require_once '../pcdl/html_head.php';
?>
<body>
<style>
#step3{
	background-color: #f1e7a4;
}
body{
    font-size:unset;
}
</style>
<?php
require_once 'nav_bar.php';
?>
<div style="margin:1em;background-color:#f1e7a4;">
生成字典数据库。约需要10分钟。
</div>
<div>
<?php
$dbfile[]=array(_FILE_DB_REF_,"ref.sql");
$dbfile[]=array(_FILE_DB_REF_INDEX_,"ref_index.sql");
$dir="./refdict_db/";

if(isset($_GET["index"])){
echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;background-color:yellow;">';	
	$index = $_GET["index"];
	$dns = "sqlite:".$dbfile[$index][0];
	$dbh = new PDO($dns, "", "",array(PDO::ATTR_PERSISTENT=>true));
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	//建立数据库
	$_sql = file_get_contents($dir.$dbfile[$index][1]);
	$_arr = explode(';', $_sql);
	//执行sql语句
	foreach ($_arr as $_value) {
		$dbh->query($_value.';');
	}
	echo $dns."建立数据库成功";
echo "</div>"; 
}


for($i=0; $i<count($dbfile); $i++){
	$db = $dbfile[$i];
	echo '<div style="padding:10px;margin:5px;border-bottom: 1px solid gray;display:flex;">';
	echo '<div style="flex:5;">'.$db[0].'</div>';
	echo '<div style="flex:3;">';
	if(!file_exists($db[0])){
        echo "<span style='color:red;'>数据库不存在</span>";
    	echo "</div>"; 
        echo '<div style="flex:2;"><a href="step3.php?index='.$i.'">建立</a></div>';    
	}
	else{
        echo "<span style='color:green;'>已存在</span>";
    	echo "</div>"; 
        echo '<div style="flex:2;"><a href="step3.php?index='.$i.'">重建</a><span style="color:red;">注意！此操作将删除原数据库中所有数据！</span></div>';
    }
	echo "</div>";  
}
?>
</div>
<div id="response"></div>
<script>
<?php
echo "var rich_file_list = new Array();\n";
$filelist = array();
if(($handle=fopen(_DIR_DICT_TEXT_.'/rich/list.txt','r'))!==FALSE){
	while(($data=fgetcsv($handle,0,','))!==FALSE){
        $filelist[] = $data;     
    }
    foreach($filelist as $value){
        echo "rich_file_list.push(['{$value[0]}','{$value[1]}']);\n";
    }    
}
else{
    exit("无法打开rich文件列表");
}



echo "var sys_file_list = new Array();\n";
$filelist = array();
if(($handle=fopen(_DIR_DICT_TEXT_.'/system/list.txt','r'))!==FALSE){
	while(($data=fgetcsv($handle,0,','))!==FALSE){
        $filelist[] = $data; 
    }
    foreach($filelist as $value){
        echo "sys_file_list.push(['{$value[0]}','{$value[1]}']);\n";

    }    
}
else{
    exit("无法打开system文件列表");
}



echo "var thin_file_list = new Array();\n";
$filelist = array();
if(($handle=fopen(_DIR_DICT_TEXT_.'/thin/list.csv','r'))!==FALSE){
	while( ($data=fgetcsv($handle,0 , ',' ) )!==FALSE){
        $filelist[] = $data; 
    }
    foreach($filelist as $value){
        echo "thin_file_list.push(['{$value[0]}','{$value[1]}','{$value[2]}']);\n";
    }    
}
else{
    exit("无法打开thin文件列表");
}



?>
var iCurrRichDictIndex = 0;
var iCurrSysDictIndex = 0;
function run_rich_dict(index){
    if(index >= rich_file_list.length){
        $("#response").html($("#response").html()+"rich dict Down");
        run_sys_dict(0);
    }
    else{
        $.get("step3_run.php",
        {
            dbtype:"rich",
            filename:rich_file_list[index][0],
            dbname:rich_file_list[index][1]
        },
        function(data,status){
            $("#response").html($("#response").html()+data+"<br>");
            iCurrRichDictIndex++;
            run_rich_dict(iCurrRichDictIndex);
        });
    }
}

function run_sys_dict(index){
    if(index >= sys_file_list.length){
        $("#response").html($("#response").html()+"All Down");
    }
    else{
        iCurrSysDictIndex = index;
        $.get("step3_run.php",
        {
            dbtype:"system",
            filename:sys_file_list[index][0],
            dbname:sys_file_list[index][1]
        },
        function(data,status){
            $("#response").html($("#response").html()+data+"<br>");
            iCurrSysDictIndex++;
            run_sys_dict(iCurrSysDictIndex);
        });
    }
}

var iCurrThinDictIndex=0;
function run_ref_dict(index){
    if(index >= thin_file_list.length){
        $("#response").html($("#response").html()+"All Down");
    }
    else{
        iCurrThinDictIndex = index;
        $.get("step3_run.php",
        {
            dbtype:"thin",
            filename:thin_file_list[index][0],
            dbname:thin_file_list[index][1],
            table:thin_file_list[index][2]
        },
        function(data,status){
            $("#response").html($("#response").html()+data+"<br>");
            iCurrThinDictIndex++;
            run_ref_dict(iCurrThinDictIndex);
        });
    }
}

function run_part_dict(){
        $.get("step3_run.php",
        {
            dbtype:"part",
            filename:"",
            dbname:""
        },
        function(data,status){
            $("#response").html($("#response").html()+data+" done<br>");
        });
}
</script>

<h2>


</h2>

<div class="card">
	<h4>第三方参考字典</h4>
	<div class="contence">
	<button onclick="run_ref_dict(0)">Build rich dictionary </button><span style="请注意！此操作将会覆盖原有数据库。"><br>
	</div>
</div>

<div class="card">
	<h4>标准格式字典</h4>
	<div class="contence">
	<button onclick="run_rich_dict(0)">Build rich dictionary </button><span style="请注意！此操作将会覆盖原有数据库。"><br>
	</div>
</div>

<div class="card">
	<h4>组分字典</h4>
	<div class="contence">
	    <button onclick="run_part_dict()">Build part dictionary</button><span style="请注意！此操作将会覆盖原有数据库。">
	</div>
</div>
<hr>
<h2><a href="step4.php">[Next]</a></h2>
</body>
</html>