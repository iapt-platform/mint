<script>
		

	
</script>
	<style>
		.icon_btn{
    color:var(--btn-color);
    padding: 0.3em 0.3em;
    border: 1px solid var(--nocolor);
    border-radius: 6px;
    margin: 0 2px;
}

.icon_btn:hover{
    background-color: var(--btn-hover-bg-color);
    border: 1px solid var(--btn-border-line-color);
}

		/* 容器 <div> - 需要定位下拉内容 */
.dropdown {
    position: relative;
    display: inline-flex;
}



/* 下拉内容 (默认隐藏) */
.dropdown-content {
    border-radius: 3px;
    display: none;
    position: absolute;
    background-color: var(--tool-bg-color2);
    min-width: 60px;
    box-shadow: 0px 3px 16px 0px var(--shadow-color);
    z-index: 6;
    top: 100%;
    right: 0;
}




/*使用一半宽度 (120/2 = 60) 来居中提示工具*/
.dropdown-content::after {
    content: " ";
    position: absolute;
    bottom: 100%;
    right: 0;
    margin-right: 20px;
    border-width: 5px;
    border-style: solid;
    border-color: transparent transparent var(--tool-bg-color2) transparent;
}



/* 下拉菜单的链接 */
.dropdown-content a {
    padding: 6px 6px;
    text-decoration: none;
    display: block;
    white-space: nowrap;
	color: var(--tool-color);
}



/* 鼠标移上去后修改下拉菜单链接颜色 */
.dropdown-content a:hover {
    background-color: var(--btn-hover-bg-color);
    color: var(--btn-hover-color);
    border-radius: 3px;
}


/* 当下拉内容显示后修改下拉按钮的背景颜色 */
.dropdown:hover .icon_btn {
    background-color: var(--btn-hover-bg-color);
    border-color: var(--btn-border-line-color);
}

.dropdown:hover .icon {
    fill: var(--btn-hover-color);
}

.index_toolbar {
    height: 3.5em;
    width: 100%;
    top: 0;
    left: 0;
    margin: 0;
    /*text-align: center;*/
    padding: 0 10px;
    border-bottom: 1px solid var(--input-bg-color);
    background-color: var(--tool-bg-color);
    display: -webkit-flex;
    display: -moz-flex;
    display: flex;
    -webkit-align-items: center;
    -moz-align-items: center;
    align-items: center;
    -webkit-justify-content: space-between;
    -moz-justify-content: space-between;
    justify-content: space-between;
    z-index: 2

}
	.head_nav{
		display: flex;
    padding: 0 1em;
    color: var(--btn-color);
    font-size: 1.2em;
    font-weight: 300;
	}	
	.head_nav li{
		display: inline-flex;
    padding: 0 0.5em;
    align-items: center;
	}

	.nav_link, .nav_link:link, .nav_link:visited {
		color:var(--btn-color);
	}
	.nav_link:hover{
    	color: var(--btn-border-color);
	}

	.head_nav_dropdown_content{
		position: absolute;
		background-color: var(--tool-bg-color);
		display:none;
		margin-top:5em;
	}
	.head_nav_dropdown_content li{
		display:block;
	}
	.head_nav_dropdown:hover .head_nav_dropdown_content{
		display:block;
	}

	#pre_search_result {
    position: absolute;
    background-color: var(--btn-hover-bg-color);
    border: 1px solid var(--btn-border-line-color);
    border-radius: 5px;
    max-width: 100%;
    width: 50em;
	display:none;
}
.pre_serach_block{
	border-bottom: 1px solid var(--shadow-color);
    padding: 5px 8px;
}
.pre_serach_block_title{
	display:flex;
	justify-content: space-between;
}
.pre_serach_content{
	padding: 4px 4px 4px 15px;
}

.lab_tab{
	display:flex;
}
.lab_tab>li{
	padding:5px;
}

#user_info {
		background-color: var(--bg-color);
		color: var(--main-color);
	}
	</style>
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">

				<ul class="head_nav">
					<li>
						<a href="../pcdl">
						<svg class="icon" style="height: 2.7em;width: 6.8em;padding-top: 15px;">
							<use xlink:href="../public/images/svg/wikipali_without_studio.svg#wikipali_without_studio"></use>
						</svg>
						</a>

						<div class="head_nav_dropdown">
							Libary <span style="margin-left:8px;font-size:20%;">▼</span>
							<ul class="head_nav_dropdown_content" style="margin-top:0;">
								<li><a class="nav_link" href="../pcdl">Libary</a></li>
								<li><a class="nav_link" href="../studio" target="_blank">Studio</a></li>
							</ul>
						</div>
					</li>
					<li><a class="nav_link" href="../palicanon">三藏</a></li>
					<li><a class="nav_link" href="../course">课程</a></li>
					<li><a class="nav_link" href="../wiki">百科</a></li>
					<li><a class="nav_link" href="../dict">字典</a></li>
					<li class="nav_link head_nav_dropdown" id="">
						<div>更多</div>
						<ul class="head_nav_dropdown_content">
							<li><a class="nav_link" href="../pc">书摘</a></li>
							<li><a class="nav_link" href="../course">著作</a></li>
							<li><a class="nav_link" href="../wiki">佛历</a></li>
							<li><a class="nav_link" href="../wiki">巴利编码转换</a></li>
							<li><a class="nav_link" href="../statistics">字符统计</a></li>
						</ul>
					</li>
				</ul>			
			</div>

			<div >
			</div>
			<div class="toolgroup1">
			<!--<input id="search_input" type="input" placeholder="搜索"  style="margin-left: 0.5em;padding:4px;width: 40em;max-width: 80%" >-->
			<a href="../search">[搜索]</a>
			<?php 

			include "../ucenter/user.php";
			include "../lang/lang.php";			
			?>			
			</div>
		</div>	
		<!--tool bar end -->
		