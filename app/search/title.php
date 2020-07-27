<?PHP
include "../pcdl/html_head.php";
?>
<body>

<?php
	require_once("../pcdl/head_bar.php");
	require_once("../search/toobar.php");
?>
    <style>
        #dt_title{
            border-bottom: 2px solid var(--link-hover-color);
        }
    </style>
	<script language="javascript" src="title.js"></script>

	<div id="dict_ref_search_result" style="background-color:white;color:black;">
	</div>
	
    <?php
    if(!empty($_GET["key"])){
        echo "<script>";
        echo "dict_pre_word_click(\"{$_GET["key"]}\")";
        echo "</script>";
    }
    ?>
<?php
include "../pcdl/html_foot.php";
?>
