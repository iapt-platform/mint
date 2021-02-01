<?PHP
include "../pcdl/html_head.php";
?>
<body>

<?php
    require_once("../pcdl/head_bar.php");
?>
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
		margin: 2em 0;
	}
	
	.index_list_categories a:hover{
		color: var(--tool-link-hover-color);
	}
	/*.index_list_categories a,a:link{
		color: var(--main-color);
	}*/
	.index_list_categories button{
		border: none;
	}
	
	.pd-10{
		padding:10px;
	}
	.card li{
		white-space: unset;
	}
	.card code{
		white-space: unset;
	}
	</style>
<div class="index_inner" >
    
	<div class="index_list_categories">
		<div class="title_bar">
			<span class="title h3"><?php echo $_local->gui->speaker ?></span>	
			<span class="title_more"><a href="../course"><?php echo $_local->gui->more ?></a></span>
		</div>
		<div class="content">
			<div id="course_list_new" class="content_inner">



			</div>
		</div>
	</div>
	<script>
	$.get("../course/teacher_list.php",function(data,status){
		let xDiv = document.getElementById("course_list_new");
		if(xDiv){
			xDiv.innerHTML=data;
		}
	});
	</script>
	<div class="course_block">	
		<div class="title" >
		连载中
		</div>
		<div id="course_list_ongoing">
		</div >
	</div>	

	<div class="course_block">	
		<div class="title" >
		已完结
		</div>
		<div id="course_list_complete">
		</div >
	</div>

    <script>
	$.get("../course/course_list.php",function(data,status){
        let arrData = JSON.parse(data);
        let html_complete="";
        let html_ongoing="";
		
        for (const iterator of arrData) {
			let html="";
            html += '<div class="card" style="display:flex;margin:1em;padding:10px;">';
            html += '<div style="flex:7;">';
            html +=  '<div class="title" style="padding-bottom:5px;font-size:110%;font-weight:600;"><a href="../course/course.php?id='+iterator.id+'">'+iterator.title+'</a></div>';
			html += '<div class="summary"  style="padding-bottom:5px;">'+iterator.subtitle+'</div>';
			let summary = "";
			try{
				summary = marked(iterator.summary);
			}
			catch(e){
			}
            html += '<div class="summary"  style="padding-bottom:5px;">'+summary+'</div>';
            html += '</div>';
            html += '<div style="flex:3;max-width:15em;">';
            html += '</div>';
            html += '</div>';
			if(iterator.status==40){
				html_complete += html;
			}
			else if(iterator.status==30){
				html_ongoing += html;
			}
        }
		$("#course_list_complete").html(html_complete);
		$("#course_list_ongoing").html(html_ongoing);
	});
	</script>	

    </div>

<?php
include "../pcdl/html_foot.php";
?>
