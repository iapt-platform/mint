<?php
require_once '../pcdl/html_head.php';
?>
<body >	

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
			<span class="title h3">圣典</span>	
			<span class="title_more"><a href="../palicanon">更多</a></span>
		</div>
		<div class="content">
			<div id="article_new" class="content_inner">

			</div>
		</div>
	</div>
	<script>
	$.get("../article/list_new.php",function(data,status){
		let xDiv = document.getElementById("article_new");
		if(xDiv){
			xDiv.innerHTML=data;
		}
	});
	</script>	
	
	<div class="index_list_categories">
		<div class="title_bar">
			<span class="title h3">课程</span>	
			<span class="title_more"><a href="../course">更多</a></span>
		</div>
		<div class="content">
			<div id="course_list_new" class="content_inner">



			</div>
		</div>
	</div>
	<script>
	$.get("../course/list_new.php",function(data,status){
		let xDiv = document.getElementById("course_list_new");
		if(xDiv){
			xDiv.innerHTML=data;
		}
	});
	</script>	

    
	<div class="index_list_categories">
		<div class="title_bar">
			<span class="title h3">百科</span>	
			<span class="title_more"><a href="../wiki">更多</a></span>
		</div>
		<div class="content">
			<div id="pali_pedia" class="content_inner">
			</div>
		</div>
	</div>
	<script>
	$.get("../term/new.php",function(data,status){
		let xDiv = document.getElementById("pali_pedia");
		if(xDiv){
			xDiv.innerHTML=data;
		}
	});
	</script>	

    </div>


	

<?php
	include "../pcdl/html_foot.php";
?>

