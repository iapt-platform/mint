<?PHP

include "../pcdl/html_head.php";
?>

<body>
    <script language="javascript" src="../course/course.js"></script>

    <?php
    require_once("../pcdl/head_bar.php");
    ?>

    <link type="text/css" rel="stylesheet" href="./style.css" />
    <link type="text/css" rel="stylesheet" href="./mobile.css" media="screen and (max-width:800px)" />


    <div id='course_content'>
        <div id='course_info_shell' class='padding_2_1rem'>
            <div id='course_info' class="section_inner"></div>
        </div>
        <div id='lesson_list_shell'>
            <div id='lesson_list' class="section_inner"></div>
        </div>
    </div>

    <script>
        course_load("<?php echo $_GET["id"]; ?>");
    </script>
    <?php
    include "../pcdl/html_foot.php";
    ?>