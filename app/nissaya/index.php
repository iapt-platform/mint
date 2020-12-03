<?php
require_once "../public/load_lang.php";
require_once "../path.php";
require_once "../pcdl/html_head.php";
?>
<body style="margin: 0;padding: 0;" class="reader_body" >
<script src="../nissaya/nissaya.js"></script>
<style>
.img_box{
	background-color:gray;
}
.book_page{
	display: inline-block;
    background-color: blue;
    max-width: 100%;
    width: 100vw;	
}
</style>
<script>
_nissaya_book = <?php echo $_GET["book"];?>;
_nissaya_para = <?php echo $_GET["para"];?>;
</script>

<div id="contence">
</div>
	</body>
</html>