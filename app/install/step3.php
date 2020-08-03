<?php
require_once '../pcdl/html_head.php';
?>

<body>
<h3>Step 3 Dictionary</h3>
<div style="margin:1em;background-color:#f1e7a4;">
生成字典数据库。约需要10分钟。您可以从网络下载已经生成好的数据库。
<a href="https://www.dropbox.com/s/naf7sk9i9sf0dfi/appdata.7z?dl=0">drobox 7z format 754MB</a>
解压缩后放在项目目录中
<pre>
[project dir]
 └app
 └appdata
   └dict
     └3rd
     └system
   └palicanon
 └user 
 </pre>
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
}
else{
    exit("无法打开rich文件列表");
}

foreach($filelist as $value){
    echo "rich_file_list.push(['{$value[0]}','{$value[1]}']);\n";

}

echo "var sys_file_list = new Array();\n";
$filelist = array();
if(($handle=fopen(_DIR_DICT_TEXT_.'/system/list.txt','r'))!==FALSE){
	while(($data=fgetcsv($handle,0,','))!==FALSE){
        $filelist[] = $data;     
	}
}
else{
    exit("无法打开system文件列表");
}

foreach($filelist as $value){
    echo "sys_file_list.push(['{$value[0]}','{$value[1]}']);\n";

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
<button onclick="run_rich_dict(0)">Build rich dictionary </button><span style="请注意！此操作将会覆盖原有数据库。"><br>
<button onclick="run_part_dict()">Build part dictionary</button><span style="请注意！此操作将会覆盖原有数据库。">
</h2>
<hr>
<h2><a href="step4.php">[Next]</a></h2>
</body>
</html>