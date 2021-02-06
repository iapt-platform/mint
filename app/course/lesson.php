<?PHP
include "../pcdl/html_head.php";
?>
<body>
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/mermaid@8.6.0/dist/mermaid.min.js"></script>

<script src="../course/lesson.js"></script>
<?php
    require_once("../pcdl/head_bar.php");
?>

<link type="text/css" rel="stylesheet" href="./style.css" />

<div id='course_content' >
    <div id='course_info_shell'><div id='course_info' class="section_inner"></div></div>
    <div id='lesson_info_shell'><div id='lesson_info' class="section_inner"></div></div>
    <div id='lesson_list_shell'><div id='lesson_list' class="section_inner lesson"></div></div>
</div>

<script>
lesson_load("<?php echo $_GET["id"]; ?>");
</script>
<?php
include "../pcdl/html_foot.php";
?>