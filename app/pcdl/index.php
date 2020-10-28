<?php
require_once '../pcdl/html_head.php';
?>
<body >	
<script language="javascript" src="../pcdl/index.js"></script>

<style>
	.content_block{
		flex: 0 0 auto;
		width: 25%;
		padding: 10px;
	}

	.index_inner{
		width: 960px;
		margin-left: auto;
		margin-right: auto;
	}
	.content_inner{
		display:flex;
	}
	.h3{
		font-size: 16px;
	}
	.index_list_categories{
		margin-bottom: 2em;
	}
	
	.index_list_categories a:hover{
		color: var(--tool-link-hover-color);
	}
	.index_list_categories a,a:link{
		color: var(--main-color);
	}
	.index_list_categories button{
		border: none;
	}
	
	.pd-10{
		padding:10px;
	}
	</style>
	<a name="pagetop"></a>	
	<!-- tool bar begin-->
<?php
    require_once("head_bar.php");
?>
	<!--tool bar end -->


<div class="index_inner" >

<div class="index_list_categories">
		<div class="title_bar">
			<span class="title h3">Collect</span>	
			<span class="title_more"><a href="../collect">more</a></span>
		</div>
		<div class="content">
			<div id="article_new" class="content_inner">
			</div>
		</div>
	</div>
	
	<div class="index_list_categories">
		<div class="title_bar">
			<span class="title h3">Course</span>	
			<span class="title_more"><a href="../course">more</a></span>
		</div>
		<div class="content">
			<div id="course_list_new" class="content_inner">
			</div>
		</div>
	</div>


    
	<div class="index_list_categories">
		<div class="title_bar">
			<span class="title h3">Pali Term</span>	
			<span class="title_more"><a href="../wiki">more</a></span>
		</div>
		<div class="content">
			<div id="pali_pedia" class="content_inner">
			</div>
		</div>
	</div>


    </div>

	<script>
	$(document).ready(function(){
		index_onload();
});


	</script>	
	

<?php
	include "../pcdl/html_foot.php";
?>

