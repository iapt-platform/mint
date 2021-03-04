<?PHP
if(isset($_GET["userid"])){
    setcookie("teacher_id", $_GET["userid"], time()+60*60,"/");
}

include "../pcdl/html_head.php";
?>
<body>

<?php
    require_once "../path.php";
    require_once "../public/_pdo.php";
    require_once '../public/function.php';
    require_once '../ucenter/function.php';
    require_once "../pcdl/head_bar.php";
    $currChannal = "course";
    require_once "../uhome/head.php";
?>

<div class='index_inner' >
<div id="course_list" style='display:flex;'>
</div>
</div>
<script src="../public/js/marked.js"></script>
<script>
	$.get("../course/course_list.php",
    {
    teacher:"<?php echo $_GET["userid"]; ?>"
    },
    function(data,status){
        let arrData = JSON.parse(data);
        let html='<iframe style="width: 100%;height: 67em;flex:5" src="../fullcalendar/examples/time-zones.php"></iframe>';
        html += '<div style="flex: 4">';

        for (const iterator of arrData) {

            html += '<div class="card" style="display:flex;margin:1em;padding:10px;">';

            html += '<div style="flex:7;">';
            html +=  '<div class="title" style="padding-bottom:5px;font-size:110%;font-weight:600;"><a href="../course/course.php?id='+iterator.id+'">'+iterator.title+'</a></div>';
            html += '<div class="summary"  style="padding-bottom:5px;">'+iterator.subtitle+'</div>';
            html += '<div class="summary"  style="padding-bottom:5px;overflow: hidden;text-overflow: ellipsis;display: -webkit-box;-webkit-box-orient: vertical;-webkit-line-clamp: 5;">'+marked(iterator.summary)+'</div>';

            html += '</div>';

            html += '<div style="flex:3;max-width:15em;">';

            html += '</div>';

            html += '</div>';
        }
		html += '</div>';
		$("#course_list").html(html);

	});
</script>
<?php
include "../pcdl/html_foot.php";
?>