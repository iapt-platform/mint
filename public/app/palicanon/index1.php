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
require_once "../public/_pdo.php";
require_once '../media/function.php';
require_once '../public/function.php';
?>

<link href="../../node_modules/jquery.fancytree/dist/skin-win7/ui.fancytree.css" rel="stylesheet" type="text/css" class="skinswitcher">
<script src="../tree/jquery.fancytree.js" type="text/javascript"></script>

	<script src="../widget/like.js"></script>
	<link type="text/css" rel="stylesheet" href="../widget/like.css"/>
	<script src="../palicanon/chapter_channel.js"></script>
	<link type="text/css" rel="stylesheet" href="../palicanon/loading.css"/>

<style>
.chapter_list ul {
    margin-left: 0;
}
.head_bar{
    display: flex;
    flex-direction: column;
}
#left-bar{
    flex: 2;
    background-color: var(--box-bg-color2);
}

.chapter_list ul li .main{
    display:flex;
}
.book_view  ul li{
    display:block;
}
.book_view .level_1{
    background:unset;
}

.book_view ul li{
    border:unset;
    width: 30%;
    height:90px;
}
.chapter_list .list {
    display: none;
}
.chapter_list .show {
    display: block;
    width: 100%;
}
.chapter_list .grid {
    width: 100%;
}
.chapter_list > div {
    max-height: unset;
    overflow-y: unset; 
}

.chapter_list .more_info{
    display:flex;
    font-size:80%;
    color: var(--main-color1);
    justify-content: space-between;
}


}
.more_info>.palicanon_chapter_info>.item{
    margin-right:1em;
}
.left_item>.item{
    margin-right:1em;
}
.filter>.inner {
    max-height: 200px;
    overflow-y: auto;
    background-color: var(--input-bg-color);
}

.main_menu {
    font-size: 100%;
    text-align: center;
    margin: 0 1em;
    transition: all 600ms ease;
    text-transform: capitalize;
}
.main_menu>span {
    margin: 2px;
    padding: 2px 12px;
    font-weight: 500;
    transition-duration: 0.2s;
    cursor: pointer;
    font-size: 120%;
    border: unset;
    border-radius: 0;
    border-bottom: 2px solid var(--nocolor);
    display: inline-block;
}
.main_menu>.select {
    border-bottom: 2px solid var(--link-color);
}
.main_menu>span>a {
    color:unset;
}
.main_menu span:hover {
    background-color: unset;
    color: unset;
    border-color: var(--link-hover-color);
}
select#tag_category_index option {
    background-color: gray;
}
button.active {
    background-color: gray;
}

.chapter_list ul li>.main>.left{
    width: 100px;
    display: flex;
    flex-direction: row;
    justify-content: space-between;
}
.chapter_list ul li>.main>.right{
    width:100%;
}
.chapter_tag {
    width: 475px;
    padding: 5px 0;
    overflow-y: visible;
    overflow-x: auto;
    display: flex;
    flex-wrap: wrap;
}
.left_item {
    margin: 4px 0;
}
.left_item>.item>.small_icon{
    width:16px;
    height:16px;
}
.left_item>.item>.text{
    padding:5px;
}

div#tag_list {
    background-color: var(--btn-color);
    padding: 5px;
    display: none;
}

#more_chapter {
    text-align: center;
}
#more_chapter_line {
    border-bottom: 1px solid var(--border-line-color);
    height: 1em;
}
#btn_more_chapter{
        position: absolute;
    margin-top: -1.1em;
    background-color: var(--link-color);
    color: var(--bg-color);
    border: none;
    padding: 2px 40px;
    margin-left: -5em;
}
#filter-author li.active{
    background-color:gray;
}

#filter_bar {
    display: flex;
    justify-content: space-between;
}
div#filter_bar {
    font-size: 120%;
}

span.channel:hover {
    background-color: wheat;
}
span.channel {
    cursor: pointer;
}
</style>

<?php
    if(isset($_GET["view"])){
        $_view = $_GET["view"];
    }else{
        $_view = "community";
    }
?>

<div style="display:flex;">
    <div id='left-bar' >
        <div id='left-bar-inner'>
            <div class="filter submenu">
                <div class="title submenu_title" style="flex;">
                    <span>分类标签</span>
                    <span>
                        <select id="tag_category_index" onchange="TagCategoryIndexchange(this)"></select>
                    </span>
                </div>
                <div class='inner' style='max-height: unset;'>
                    <div id='tag-category' ></div>
                </div>
            </div>
            <div class="filter submenu">
                <div class="title submenu_title">作者</div>
                <div class='inner' id='filter-author' ></div>
            </div>
            <div class="filter submenu">
                <div class="title submenu_title">语言</div>
                <div class='inner' id='filter-lang' ></div>
            </div>
            <div class="filter submenu">
                <div class="title submenu_title">类型</div>
                <div class='inner' id='filter-type' ></div>
            </div>
        </div>
    </div>
    <div id='course_head_bar' style='flex:6;padding:0 10px 10px 10px;'>
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
                <div class='main_menu' id = 'main_menu'>

                </div>
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

            <div id="select_bar" >
                <div id="channel_selected"></div>
                <div id="tag_selected"></div>
            </div>

            <div id='bread-crumbs'></div>
            <div id='filter_bar'>
                <div id='filter_bar_left'></div>
                <div id='filter_bar_right'>
                    <button id='btn-filter' onclick="tag_list_slide_toggle(this)">
                        <svg class='icon' style='fill: var(--box-bg-color1)'>
                        <use xlink:href='../../node_modules/bootstrap-icons/bootstrap-icons.svg#filter'>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <div id="tag_list" style='display:none;'>
                    <div id="tag_list_head" style="display:flex;justify-content: space-between;border-bottom: 1px solid var(--border-line-color);">
                        <div style='width:20em;'>
                            <input id="tag_input" type="input" placeholder="tag search" size="20">
                        </div>
                        <div>
                            <button id="btn-tag_list_close" onclick='close_tag_list()'>X</button>
                        </div>
                    </div>
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
            <div class='index_inner'>
                <div id="chapter_shell" class="chapter_list" >
                    <div id="list_shell_1" class="show" level="1">
                        <ul id="list-1" class="grid" level="1" >
                        </ul>
                        <button>More</button>
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
    <div style="flex:2;">
    <div class='bangdan' id = "user_recent">
        <div class='title'>最近阅读</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    <div class='bangdan'>
        <div class='title'>求助</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    <div class='bangdan'>
        <div class='title'>社区推荐</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    <div class='bangdan' id='contribution'>
        <div class='title'>月度贡献</div>
        <div class='list'>
            <ul>
                <li>zuixin-1</li>
            </ul>
        </div>
    </div>
    </div>
</div>



    <script>
        $(document).ready(function() {
            
            let indexFilename = localStorage.getItem('palicanon_tag_category');
            if(!indexFilename){
                indexFilename = "defualt";
            }
            loadTagCategory(indexFilename);
            loadTagCategoryIndex();
            <?php
            if(isset($_GET["view"])){
                echo "_view = '{$_GET["view"]}';";
            }
            
            switch ($_view) {
                case 'community':
                    echo "community_onload();";
                    break;
                case 'category':
                    echo "palicanon_onload();";
                    break;
                case 'my';
                default:
                    # code...
                    break;
            }
            ?>
            ReanderMainMenu();
            updateFirstListView();
            loadUserRecent();
            loadContribution();
        });
    </script>
    <?php
include "../pcdl/html_foot.php";
?>