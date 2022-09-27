<?PHP
require_once "../pcdl/html_head.php";
?>

<body>
    <script src="../palicanon/palicanon.js"></script>
    <script src="../term/term.js"></script>

<?php
    require_once "../pcdl/head_bar.php";
?>

	<link type="text/css" rel="stylesheet" href="../palicanon/style.css" />
	<link type="text/css" rel="stylesheet" href="../palicanon/style_mobile.css" media="screen and (max-width:800px)">


    <script>
        var tag_level = <?php echo file_get_contents("../public/book_tag/tag_list.json"); ?>;
    </script>
<?php
//

require_once "../config.php";
require_once '../media/function.php';
require_once '../public/function.php';
?>

<link href="../../node_modules/jquery.fancytree/dist/skin-win7/ui.fancytree.css" rel="stylesheet" type="text/css" class="skinswitcher">
<script src="../tree/jquery.fancytree.js" type="text/javascript"></script>

	<script src="../widget/like.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/like.css"/>
	<script src="../palicanon/chapter_channel.js"></script>
	<link type="text/css" rel="stylesheet" href="../palicanon/chapter_channel.css"/>
	<link type="text/css" rel="stylesheet" href="../palicanon/loading.css"/>

    <script src="../palicanon/router.js"></script>
    <script src="../palicanon/test.js"></script>
    <script src="../palicanon/my_space.js"></script>

<style>


</style>

<?php
    if(isset($_GET["view"])){
        $_view = $_GET["view"];
    }else{
        $_view = "community";
    }
?>

<div id='main_view' >
    <div id='left-bar' >
        <div id='left-bar-inner'>
            <div class="filter submenu">
                <div class="title submenu_title" style="flex;">
                    <span>分类</span>
                    <span>
                        <select id="tag_category_index" onchange="TagCategoryIndexchange(this)"></select>
                    </span>
                </div>
                <div class='inner' style='max-height: unset;'>
                    <div id='tag-category' ></div>
                </div>
            </div>
            <div class="filter submenu">
                <div class="title submenu_title"><span>作者</span></div>
                <div class='inner' id='filter-author' >
                    <div  class="lds-ellipsis" ><div></div><div></div><div></div><div></div></div>
                </div>
            </div>
        </div>
    </div>
    <div id='course_head_bar' >
        <div class='index_inner '>
            <div style='display:flex;justify-content: space-between;display:none;'>
                <div> </div>
                <div style=''>
                    <select onchange='viewChanged(this)'>
                        <option value='list'>列表</option>                    
                        <option value='card'>卡片</option>
                    </select>
                </div>
            </div>
			<div>
				<div id='tag_list_setting_div'>
                            
                    <div class='inner' id='filter-setting' style='display: flex;'>
							<button id='btn-filter' onclick="tag_list_slide_toggle(this)">
								标签过滤
                    		</button>
							<div class='settting-item'>
								<span></span>
								<span>
									<select id='setting_lang'>
										<option value='auto'>自动选择语言</option>
										<option value=''>全部语言</option>
										<option value='zh'>中文</option>
										<option value='en'>英文</option>
									</select>
								</span>
							</div>
                                <div class='settting-item'>
                                    <span></span>
                                    <span>
                                        <select id='setting_channel_type'>
                                            <option value=''>全部类型</option>
                                            <option value='translation' selected >译文</option>
                                            <option value='nissaya'>Nissaya</option>
                                            <option value='commentray'>注疏</option>
                                        </select>
                                    </span>
                                </div>
                                <div class='settting-item'>
                                    <span>完成度</span>
                                    <span>
                                        <select id='setting_progress'>
                                            <option value='0.9'>90</option>
                                            <option value='0.8'>80</option>
                                            <option value='0.5'>50</option>
                                            <option value='0.2'>20</option>
                                        </select>
                                    </span>
                                </div>
                                <div style='display:flex;justify-content: space-between;'><button>还原默认</button><button onclick="updateSetting()">应用</button></div>
                            </div>
                        </div>
                </div>			
			</div>
            <div>
                <div class='main_menu' id = 'main_menu'>

            </div>
            <div id="main_tag"  style="display:none;">
                <span tag="sutta" title="sutta"></span>
                <span tag="vinaya"  title="vinaya"></span>
                <span tag="abhidhamma" title="abhidhamma"></span>
                <span tag="mūla" title="mūla"></span>
                <span tag="aṭṭhakathā" title="aṭṭhakathā"></span>
                <span tag="ṭīkā" title="ṭīkā"></span>
                <span tag="añña" title="añña"></span>
            </div>
            <div>
                <div id="tag_list" style='display:none;'>
                    <div id="tag_list_head" style="display:flex;justify-content: space-between;border-bottom: 1px solid var(--border-line-color);">
                        <div style='width:20em;'>
                            
                        </div>
                        <div>
                            <button id="btn-tag_list_close" onclick='close_tag_list()'>X</button>
                        </div>
                    </div>

                    <div id='tag_list_inner'>
                        <div id='tag_list_tag_div'>
                            <h2>标签</h2>
                            <div><input id="tag_input" type="input" placeholder="tag search" size="20"></div>
                            <div level="0" class="tag_others"></div>
                            <div level="1" class="tag_others"></div>
                            <div level="2" class="tag_others"></div>
                            <div level="3" class="tag_others"></div>
                            <div level="4" class="tag_others"></div>
                            <div level="5" class="tag_others"></div>
                            <div level="100" class="tag_others"></div>
                            <div level="8" class="tag_others"></div>
                        </div>

                    </div>
                </div>
            </div>
            <div id="select_bar" >
                <div id="select_bar_home" onclick='categoryGoHome()'>
                <span>
                    <svg class='icon' style='fill: var(--box-bg-color1)'>
                        <use xlink:href='../../node_modules/bootstrap-icons/bootstrap-icons.svg#house'>
                    </svg>
                </span>
                <span>
                    <svg class='icon' style='fill: var(--box-bg-color1)'>
                        <use xlink:href='../../node_modules/bootstrap-icons/bootstrap-icons.svg#chevron-compact-right'>
                    </svg>
                </span>
                </div>
                <div id="channel_selected"></div>
                <div id="tag_selected"></div>
            </div>

            <div id='palicanon-category'></div>

            
            <div id='filter_bar'>
                <div id='filter_bar_left'></div>
                <div id='filter_bar_right'>

                </div>
            </div>

			<div id="index_div">
				<div id='file_background'></div>
				<div id = "file_list_div">
					<div id='bread-crumbs'></div>
					<div class='index_inner'>
						<div id="chapter_shell" class="chapter_list" >
							<div id="list_shell_1" class="show" level="1">
								<ul id="list-1" class="grid" level="1" >
								</ul>
							</div>

							<div id="list_shell_2" level="2">
								<ul id="list-2" class="hidden" level="2"  >
								</ul>
							</div>

							<div id="list_shell_3" level="3">
								<ul id="list-3" class="hidden" level="3" >
								</ul>
							</div>

							<div id="list_shell_4" level="4">
								<ul id="list-4" class="hidden" level="4" >
								</ul>
							</div>

							<div id="list_shell_5" level="5">
								<ul id="list-5" class="hidden" level="5" >
								</ul>
							</div>

							<div id="list_shell_6" level="6">
								<ul id="list-6" class="hidden" level="6" >
								</ul>
							</div>

							<div id="list_shell_7" level="7">
								<ul id="list-7" class="hidden" level="7" >
								</ul>
							</div>

							<div id="list_shell_8" level="8">
								<ul id="list-8" class="hidden" level="8" >
								</ul>
							</div>

						</div>
					</div>
				</div>
			</div>
        </div>
    </div>
    <div style="flex:2;">
    <div class='bangdan' id = "user_recent">
        <div class='title'>最近阅读</div>
        <div class='list'>
            <div class="lds-ellipsis" ><div></div><div></div><div></div><div></div></div>
        </div>
    </div>
    <div class='bangdan' id='contribution'>
        <div class='title'>月度贡献</div>
        <div class='list'>
            <div class="lds-ellipsis" ><div></div><div></div><div></div><div></div></div>
        </div>
    </div>
    </div>
</div>



    <script>
        $(document).ready(function() {
			$("#nav_community").addClass('active');
            
            let indexFilename = localStorage.getItem('palicanon_tag_category');
            if(!indexFilename){
                indexFilename = "defualt";
            }
            loadTagCategory(indexFilename);
            loadTagCategoryIndex();
            loadFilterSetting();//载入上次的过滤器配置
            LoadAllLanguage();
			_view = 'community';
            updataHistory();
            <?php

            if(isset($_GET["tag"])){
                echo "_tags = '{$_GET["tag"]}';";
            }
            if(isset($_GET["channel"])){
                echo "_channel = '{$_GET["channel"]}';";
            }
            
            switch ($_view) {
                case 'community':
                    //echo "community_onload();";
                    break;
                case 'category':
                    //echo "palicanon_onload();";
                    break;
                case 'my';
                default:
                    # code...
                    break;
            }
            ?>
            list_tag = _tags.split(',');
            refresh_selected_tag();
            //ReanderMainMenu();
            updateFirstListView();
            //载入用户最近的阅读列表
            loadUserRecent();
            loadContribution();
			//TODO 处理标签搜索
			$("#tag_input").keypress(function () {
				tag_render_others();
			});
        });
    </script>
<?php
include "../pcdl/html_foot.php";
?>