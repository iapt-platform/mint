<?PHP
include "../pcdl/html_head.php";
?>

<body>

<?php
    require_once "../config.php";
    require_once "../public/_pdo.php";
    require_once '../public/function.php';
    require_once '../ucenter/function.php';
	require_once "../pcdl/head_bar.php";
	$currChannal = "index";
    require_once "../uhome/head.php";
?>

<div class='section_inner'>
	<div id='bio' class='course_info_block'></div>
	<div id='wikipali_step' class='course_info_block'></div>
</div>

<script>
$(document).ready(function(){
  getUserBio('<?php if(isset($_GET["userid"])){echo $_GET["userid"];} ?>')
});
</script>
<?php
include "../pcdl/html_foot.php";
?>