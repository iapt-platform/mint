<?PHP

include "../pcdl/html_head.php";
?>
<body>
<script language="javascript" src="../course/course.js"></script>

<?php
    require_once("../pcdl/head_bar.php");
?>

<style>
    #main_video_win iframe{
        width:100%;
        height:100%;
    }

    #course_frame{
        display:flex;
    }
    #course_content{

        margin:0;
        padding:0;
    }
    #lesson_list{

    }
    #course_info_head_1{
        display:flex;
    }
    #course_info_head_face{
        width: 200px;
        height: 200px;
        padding: 12px;
    }
    #course_info_head_face img{
        width: 100%;
        border-radius: 12px;
    }
    .section_inner{
        max-width: 960px;
        margin: 0 auto;
    }
    #course_info_head_title{
        padding: 12px;
    }
    #course_title{
        font-size: 22px;
    font-weight: 700;
    }

    #course_subtitle {
        font-size: 13px;
        font-weight: 600;
        padding: 10px 0;
    }
    #lesson_list_shell{
        
    background-color: var(--drop-bg-color);

    }
    .course_info_block{
        border-top: 1px solid var(--box-bg-color2);
        padding: 10px 5px;
    }
    .course_info_block:first{
        border-top: none;
    }
    .course_info_block h2{
        font-size: 16px;
    }
    #lesson_list {
        display: flex;
        flex-wrap: wrap;
    }
    #lesson_list .lesson_card{
        width:50%;
        padding: 15px;
    }
    .datatime {
    
    display: inline;
    
    padding: 1px 4px;
    }
    .not_started{
        background-color: orangered;
        color: var(--bg-color);
    }
    .in_progress,.already_over{
        color: var(--btn-hover-bg-color);
    }
    .lesson_card .title {
        padding: 5px;
        font-size: 17px;
        font-weight: 600;
    }
    #course_info_head_2{
        padding:10px;
    }
</style>
    

<?php
//

require_once "../path.php";
require_once "../public/_pdo.php";
require_once '../ucenter/function.php';
require_once '../public/function.php';

?>


<div id='course_content' >
    <div id='course_info_shell'><div id='course_info' class="section_inner"></div></div>
    <div id='lesson_list_shell'><div id='lesson_list' class="section_inner"></div></div>
</div>

<script>
    course_load("<?php echo $_GET["id"]; ?>");
</script>
<?php
include "../pcdl/html_foot.php";
?>