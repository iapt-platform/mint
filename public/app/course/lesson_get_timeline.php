<?PHP
    include "../pcdl/html_head.php";
?>
<body>
<script src="../course/lesson.js"></script>

<div>
开始时间：
<div id="form_start" >

</div>
</div>
<div>
结束时间：
<div id="form_end">

</div>
</div>
<button onclick="lesson_get_timeline_submit()">submit</button>


<div id="timeline_table">
</div>

<div >
<textarea id="timeline_json">
</textarea>
</div>

<script>

<?php  
if(isset($_GET["start"])){
	$start=$_GET["start"];
}
else{
	$start=false;
}
if(isset($_GET["end"])){
	$end=$_GET["end"];
}
else{
	$end=false;
}

echo "lesson_get_timeline_settime('{$start}','start');\n";
echo "lesson_get_timeline_settime('{$end}','end');\n";
if($start && $end){
	echo "lesson_get_timeline_json('{$start}','{$end}')";
}
?>
</script>

<?php
include "../pcdl/html_foot.php";
?>